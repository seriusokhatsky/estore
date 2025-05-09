<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Payment;
use App\Jobs\ProcessOrder;
use Illuminate\Support\Facades\Queue;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;
    protected $product;
    protected $order;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->buyer = User::factory()
            ->state(['role' => 'buyer'])
            ->create();

        $this->seller = User::factory()
            ->state(['role' => 'seller'])
            ->create();

        $this->product = Product::factory()
            ->for($this->seller)
            ->create();

        $this->order = Order::factory()
            ->for($this->buyer, 'buyer')
            ->for($this->product)
            ->create();

        $this->payment = Payment::factory()
            ->for($this->order)
            ->create([
                'status' => 'pending',
                'amount' => $this->product->price,
                'payment_method' => 'credit-card'
            ]);
    }

    public function test_can_update_payment_status_to_paid(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => $this->payment->id,
            'status' => 'paid'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Payment status processed']);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'paid'
        ]);

        Queue::assertPushed(ProcessOrder::class, function ($job) {
            return $job->order->id === $this->order->id;
        });
    }

    public function test_can_update_payment_status_to_failed(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => $this->payment->id,
            'status' => 'failed'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Payment status processed']);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'failed'
        ]);

        Queue::assertPushed(ProcessOrder::class, function ($job) {
            return $job->order->id === $this->order->id;
        });
    }

    public function test_returns_validation_error_for_invalid_status(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => $this->payment->id,
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_returns_validation_error_for_nonexistent_payment(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => 99999,
            'status' => 'paid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_id'])
            ->assertJson([
                'message' => 'The selected payment id is invalid.',
                'errors' => [
                    'payment_id' => ['The selected payment id is invalid.']
                ]
            ]);
    }

    public function test_returns_validation_error_for_missing_payment_id(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'status' => 'paid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_id']);
    }

    public function test_returns_validation_error_for_missing_status(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => $this->payment->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
