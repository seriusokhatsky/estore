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
use App\Mail\OrderPaid;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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

        $this->buyer = User::factory()
            ->state(['role' => 'buyer'])
            ->create();

        $this->seller = User::factory()
            ->state(['role' => 'seller'])
            ->create();

        $this->product = Product::factory()
            ->for($this->seller)
            ->create(['price' => 99.99]);

        $this->order = Order::factory()
            ->for($this->buyer, 'buyer')
            ->create([
                'product_id' => $this->product->id,
                'status' => 'new'
            ]);

        $this->payment = Payment::create([
            'order_id' => $this->order->id,
            'status' => 'pending',
            'amount' => $this->product->price,
            'payment_method' => 'credit-card'
        ]);
    }

    public function test_webhook_can_process_payment(): void
    {
        Queue::fake();
        Mail::fake();
        Log::shouldReceive('info')->once();

        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => $this->payment->id,
            'status' => 'paid'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Payment status processed']);

        // Assert payment status was updated
        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'status' => 'paid'
        ]);

        // Assert job was dispatched
        Queue::assertPushed(ProcessOrder::class, function ($job) {
            return $job->order->id === $this->order->id;
        });
    }

    public function test_webhook_validates_payment_id(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => 99999, // Non-existent payment
            'status' => 'paid'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_id']);
    }

    public function test_webhook_validates_status(): void
    {
        $response = $this->patchJson('/api/webhooks/payment', [
            'payment_id' => $this->payment->id,
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_process_order_job_sends_email(): void
    {
        Mail::fake();

        $job = new ProcessOrder($this->order);
        $job->handle();

        Mail::assertSent(OrderPaid::class, function ($mail) {
            return $mail->hasTo($this->order->buyer->email) &&
                   $mail->order->id === $this->order->id;
        });
    }

    public function test_order_paid_email_has_correct_content(): void
    {
        $mail = new OrderPaid($this->order);

        $this->assertEquals('Order #' . $this->order->id . ' has been paid', $mail->envelope()->subject);
        $this->assertEquals($this->order->id, $mail->order->id);
    }
}
