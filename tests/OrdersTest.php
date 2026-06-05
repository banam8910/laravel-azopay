<?php

namespace Ftech\AzoPay\Tests;

use Ftech\AzoPay\Data\CreateOrderData;
use Ftech\AzoPay\Exceptions\AzoPayException;
use Ftech\AzoPay\Facades\AzoPay;
use Illuminate\Support\Facades\Http;

class OrdersTest extends TestCase
{
    public function test_it_creates_an_order_and_parses_the_response(): void
    {
        Http::fake([
            '*/api/v1/orders' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 'ord_123',
                    'transfer_code' => 'DH55',
                    'expires_at' => '2026-06-05 12:00:00',
                    'payment_info' => [
                        'account_number' => '0123456789',
                        'account_name' => 'CONG TY ABC',
                        'bank' => 'Vietcombank',
                        'qr_code_url' => 'https://img/qr.png',
                        'checkout_url' => 'https://my.azopay.vn/c/abc',
                    ],
                ],
            ]),
        ]);

        $order = AzoPay::orders()->create(
            CreateOrderData::make()
                ->amount(100_000)
                ->merchantOrderId('DH55')
                ->description('Order #55')
        );

        $this->assertSame('ord_123', $order->id);
        $this->assertSame('DH55', $order->transferCode);
        $this->assertSame('https://img/qr.png', $order->qrCodeUrl());
        $this->assertSame('0123456789', $order->paymentInfo->accountNumber);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test-key')
                && $request['amount'] === 100_000
                && $request['bank_account_id'] === '42'   // pulled from config default
                && $request['expires_in'] === 3600;
        });
    }

    public function test_it_requires_an_amount(): void
    {
        $this->expectException(AzoPayException::class);

        AzoPay::orders()->create(CreateOrderData::make()->merchantOrderId('DH1'));
    }

    public function test_it_raises_api_exceptions_on_failure(): void
    {
        Http::fake([
            '*/api/v1/orders' => Http::response(['error_message' => 'Invalid bank account'], 422),
        ]);

        $this->expectException(\Ftech\AzoPay\Exceptions\ApiException::class);
        $this->expectExceptionMessage('Invalid bank account');

        AzoPay::orders()->create(
            CreateOrderData::make()->amount(1000)->merchantOrderId('DH1')
        );
    }
}
