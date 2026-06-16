# Laravel AzoPay

Tích hợp cổng thanh toán [AzoPay](https://azopay.vn) (chuyển khoản VietQR, tự động đối soát) cho Laravel.

Package cung cấp client gọi API, tạo đơn thanh toán + QR VietQR, và một webhook endpoint đã xác thực chữ ký để nhận sự kiện biến động giao dịch và bắn ra Laravel events.

- Laravel 10 / 11 / 12 · PHP 8.1+
- Tạo order, lấy QR + thông tin chuyển khoản
- Truy vấn trạng thái order
- Liệt kê tài khoản ngân hàng
- Webhook ký HMAC-SHA256 (`X-AzoPay-Signature`) + chống xử lý trùng (idempotency)

## Cài đặt

```bash
composer require ftech/laravel-azopay
php artisan vendor:publish --tag=azopay-config
```

Cấu hình `.env`:

```dotenv
AZOPAY_API_KEY=your-api-key        # server tự nhận diện sandbox/live theo API key
AZOPAY_API_URL=https://app.azopay.vn
AZOPAY_BANK_ACCOUNT_ID=42          # ID tài khoản nhận tiền trên dashboard
AZOPAY_PAY_CODE_PREFIX=DH
AZOPAY_EXPIRES_IN=3600
AZOPAY_WEBHOOK_SECRET=whsec_xxx    # có thể nhiều secret, cách nhau bằng dấu phẩy
```

API URL mặc định: `https://app.azopay.vn`. Server tự kiểm tra sandbox/live dựa trên API key nên không cần chọn môi trường.

## Tạo đơn thanh toán

```php
use Ftech\AzoPay\Facades\AzoPay;
use Ftech\AzoPay\Data\CreateOrderData;

$order = AzoPay::orders()->create(
    CreateOrderData::make()
        ->amount(100_000)                      // VND
        ->merchantOrderId('DH' . $order->id)   // mã đối soát, khớp prefix trên dashboard
        ->description('Đơn hàng #' . $order->id)
        ->metadata(['order_id' => $order->id])
        // ->bankAccount('42')                 // bỏ qua => dùng config mặc định
        // ->expiresIn(1800)
);

$order->id;             // id đơn trên AzoPay
$order->transferCode;   // nội dung chuyển khoản
$order->qrCodeUrl();    // ảnh QR VietQR
$order->checkoutUrl();  // trang thanh toán hosted
$order->paymentInfo->accountNumber;
```

Có thể truyền thẳng mảng thay cho `CreateOrderData`:

```php
AzoPay::orders()->create([
    'amount'            => 100_000,
    'merchant_order_id' => 'DH123',
    'description'       => 'Đơn hàng #123',
]);
```

## Truy vấn order & tài khoản ngân hàng

```php
$order = AzoPay::orders()->find('ord_123');
$order->isPaid();

$accounts = AzoPay::bankAccounts()->all();   // Collection<BankAccount>
$account  = AzoPay::bankAccounts()->find('42');
```

## Webhook

Route `POST /azopay/webhook` được đăng ký tự động (đổi đường dẫn qua `AZOPAY_WEBHOOK_PATH`, hoặc tắt bằng `AZOPAY_WEBHOOK_ROUTE=false`). Endpoint sẽ:

1. Xác thực header `X-AzoPay-Signature` (`t=<ts>,v1=<hmac>`), HMAC-SHA256 trên `"{timestamp}.{body}"`.
2. Chống trùng theo `X-AzoPay-Event-Id`.
3. Bắn Laravel events.

Lắng nghe sự kiện:

```php
use Ftech\AzoPay\Events\OrderPaid;

class MarkOrderPaid
{
    public function handle(OrderPaid $event): void
    {
        $order = $event->order();                 // Ftech\AzoPay\Data\Order
        $moid  = $order->merchantOrderId;         // 'DH123'
        $paid  = $order->paidAmount;

        // ... cập nhật đơn hàng của bạn
    }
}
```

Các event có sẵn (đều extend `AzoPayWebhookReceived` — listen class này để nhận tất cả):

| Event | AzoPay type |
|---|---|
| `OrderPaid` | `order.paid` |
| `OrderUnderpaid` | `order.underpaid` |
| `OrderOverpaid` | `order.overpaid` |
| `OrderCancelled` | `order.cancelled` |
| `OrderExpired` | `order.expired` |

Đăng ký trong `EventServiceProvider`:

```php
protected $listen = [
    \Ftech\AzoPay\Events\OrderPaid::class => [
        \App\Listeners\MarkOrderPaid::class,
    ],
];
```

### Tự xử lý webhook

Muốn tự làm route riêng, tắt route mặc định và dùng middleware xác thực chữ ký:

```php
use Ftech\AzoPay\Http\Middleware\VerifyAzoPaySignature;

Route::post('/payments/azopay', PaymentWebhookController::class)
    ->middleware(VerifyAzoPaySignature::class);
```

Hoặc xác thực thủ công:

```php
AzoPay::signature()->verify($request->getContent(), $request->header('X-AzoPay-Signature'));
```

## Nội dung chuyển khoản (remark)

VietinBank và ABBANK yêu cầu thêm tiền tố `SEVQR`. Helper tự xử lý:

```php
AzoPay::remark('DH123', $bankBin);   // "DH123" hoặc "SEVQR DH123"
```

## Testing

```bash
composer install
vendor/bin/phpunit
```

Trong app của bạn, fake HTTP của Laravel để test:

```php
Http::fake(['*/api/v1/orders' => Http::response(['status' => 'success', 'data' => [...]])]);
```

## License

MIT.
