<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\ArtifactSubmission;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MuseumWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_public_blades(): void
    {
        $artifact = $this->seedCatalog();

        $this->get('/')->assertOk()->assertSee('Главное меню');
        $this->get(route('catalog.index'))->assertOk()->assertSee('Каталог предметов');
        $this->get(route('catalog.show', $artifact))->assertOk()->assertSee($artifact->title);
        $this->get(route('login'))->assertOk()->assertSee('Вход');
        $this->get(route('register'))->assertOk()->assertSee('Регистрация пользователя');
    }

    public function test_user_can_submit_artifact_and_purchase_available_item(): void
    {
        $artifact = $this->seedCatalog();
        $category = $artifact->category;
        $user = User::factory()->create();

        $this->actingAs($user->fresh())
            ->post(route('submissions.store'), [
                'category_id' => $category->id,
                'title' => 'Старинная фотография',
                'owner_name' => $user->name,
                'contact_email' => $user->email,
                'contact_phone' => '+7 900 111-11-11',
                'desired_action' => ArtifactSubmission::ACTION_DONATE,
                'description' => 'Фотография городского собрания.',
                'provenance' => 'Семейный архив.',
            ])
            ->assertRedirect(route('submissions.index'));

        $this->assertDatabaseHas('artifact_submissions', [
            'title' => 'Старинная фотография',
            'status' => ArtifactSubmission::STATUS_NEW,
            'desired_price' => null,
        ]);

        $this->actingAs($user->fresh())
            ->post(route('orders.store', $artifact), [
                'buyer_name' => $user->name,
                'buyer_email' => $user->email,
                'buyer_phone' => '+7 900 222-22-22',
                'comment' => 'Готов оплатить после подтверждения.',
            ])
            ->assertRedirect(route('orders.index'));

        $this->assertDatabaseHas('purchase_orders', [
            'artifact_id' => $artifact->id,
            'user_id' => $user->id,
            'status' => PurchaseOrder::STATUS_NEW,
        ]);

        $this->actingAs($user)->get(route('submissions.index'))->assertOk()->assertSee('Мои заявки');
        $this->actingAs($user)->get(route('orders.index'))->assertOk()->assertSee('Мои заявки на выкуп');
    }

    public function test_admin_can_open_blades_and_process_records(): void
    {
        $artifact = $this->seedCatalog();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        $submission = ArtifactSubmission::query()->create([
            'user_id' => $user->id,
            'category_id' => $artifact->category_id,
            'title' => 'Письмо коллекционера',
            'owner_name' => $user->name,
            'contact_email' => $user->email,
            'desired_action' => ArtifactSubmission::ACTION_DONATE,
            'description' => 'Рукописное письмо.',
            'status' => ArtifactSubmission::STATUS_NEW,
        ]);
        $order = PurchaseOrder::query()->create([
            'user_id' => $user->id,
            'artifact_id' => $artifact->id,
            'buyer_name' => $user->name,
            'buyer_email' => $user->email,
            'offered_price' => $artifact->sale_price,
            'status' => PurchaseOrder::STATUS_NEW,
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Админ-панель');
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk()->assertSee('Категории фонда');
        $this->actingAs($admin)->get(route('admin.artifacts.index'))->assertOk()->assertSee('Экспонаты');
        $this->actingAs($admin)->get(route('admin.submissions.index'))
            ->assertOk()
            ->assertSee('Заявки на передачу')
            ->assertSee('Принять')
            ->assertSee('Отклонить');
        $this->actingAs($admin)->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Заявки на выкуп')
            ->assertSee('Одобрить')
            ->assertSee('Оплачена');
        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Пользователи')
            ->assertSee('Заблокировать');
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()->assertSee('Отчеты');

        $this->actingAs($admin)
            ->patch(route('admin.submissions.update', $submission), [
                'category_id' => $artifact->category_id,
                'status' => ArtifactSubmission::STATUS_ACCEPTED,
                'admin_note' => 'Принято в фонд.',
            ])
            ->assertRedirect(route('admin.submissions.index'));

        $this->assertDatabaseHas('artifact_submissions', [
            'id' => $submission->id,
            'status' => ArtifactSubmission::STATUS_ACCEPTED,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $order), [
                'status' => PurchaseOrder::STATUS_PAID,
                'admin_note' => 'Оплата получена.',
            ])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertDatabaseHas('artifacts', [
            'id' => $artifact->id,
            'status' => Artifact::STATUS_SOLD,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.submissions.destroy', $submission))
            ->assertRedirect(route('admin.submissions.index'));
        $this->assertDatabaseMissing('artifact_submissions', ['id' => $submission->id]);

        $this->actingAs($admin)
            ->delete(route('admin.orders.destroy', $order))
            ->assertRedirect(route('admin.orders.index'));
        $this->assertDatabaseMissing('purchase_orders', ['id' => $order->id]);
    }

    public function test_admin_can_block_users_and_blocked_users_cannot_create_requests(): void
    {
        $artifact = $this->seedCatalog();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.block', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
        $this->assertTrue($user->fresh()->isBlocked());

        $this->actingAs($user->fresh())
            ->post(route('submissions.store'), [
                'category_id' => $artifact->category_id,
                'title' => 'Заблокированная заявка',
                'owner_name' => $user->name,
                'contact_email' => $user->email,
                'desired_action' => ArtifactSubmission::ACTION_DONATE,
                'description' => 'Не должно сохраниться.',
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('artifact_submissions', [
            'title' => 'Заблокированная заявка',
        ]);

        $this->actingAs($user->fresh())
            ->post(route('orders.store', $artifact), [
                'buyer_name' => $user->name,
                'buyer_email' => $user->email,
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseMissing('purchase_orders', [
            'user_id' => $user->id,
            'artifact_id' => $artifact->id,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.unblock', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertFalse($user->fresh()->isBlocked());
    }

    public function test_admin_cannot_accept_own_submission_but_another_admin_can(): void
    {
        $artifact = $this->seedCatalog();
        $ownerAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $reviewAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($ownerAdmin)
            ->post(route('submissions.store'), [
                'category_id' => $artifact->category_id,
                'title' => 'Заявка администратора',
                'owner_name' => $ownerAdmin->name,
                'contact_email' => $ownerAdmin->email,
                'desired_action' => ArtifactSubmission::ACTION_DONATE,
                'description' => 'Админ передает предмет в фонд.',
            ])
            ->assertRedirect(route('submissions.index'));

        $submission = ArtifactSubmission::query()
            ->where('user_id', $ownerAdmin->id)
            ->where('title', 'Заявка администратора')
            ->firstOrFail();

        $this->actingAs($ownerAdmin)
            ->from(route('admin.submissions.index'))
            ->patch(route('admin.submissions.update', $submission), [
                'category_id' => $artifact->category_id,
                'status' => ArtifactSubmission::STATUS_ACCEPTED,
                'admin_note' => 'Сам себе принимаю.',
            ])
            ->assertRedirect(route('admin.submissions.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('artifact_submissions', [
            'id' => $submission->id,
            'status' => ArtifactSubmission::STATUS_NEW,
            'artifact_id' => null,
        ]);
        $this->assertDatabaseMissing('artifacts', [
            'title' => 'Заявка администратора',
        ]);

        $this->actingAs($reviewAdmin)
            ->patch(route('admin.submissions.update', $submission), [
                'category_id' => $artifact->category_id,
                'status' => ArtifactSubmission::STATUS_ACCEPTED,
                'admin_note' => 'Принято другим администратором.',
            ])
            ->assertRedirect(route('admin.submissions.index'));

        $this->assertDatabaseHas('artifact_submissions', [
            'id' => $submission->id,
            'status' => ArtifactSubmission::STATUS_ACCEPTED,
            'reviewed_by' => $reviewAdmin->id,
        ]);
        $this->assertDatabaseHas('artifacts', [
            'title' => 'Заявка администратора',
            'owner_id' => $ownerAdmin->id,
        ]);
    }

    public function test_admin_cannot_approve_own_purchase_order_but_another_admin_can(): void
    {
        $artifact = $this->seedCatalog();
        $buyerAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $reviewAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($buyerAdmin)
            ->post(route('orders.store', $artifact), [
                'buyer_name' => $buyerAdmin->name,
                'buyer_email' => $buyerAdmin->email,
                'comment' => 'Админ хочет выкупить экспонат.',
            ])
            ->assertRedirect(route('orders.index'));

        $order = PurchaseOrder::query()
            ->where('user_id', $buyerAdmin->id)
            ->where('artifact_id', $artifact->id)
            ->firstOrFail();

        $this->actingAs($buyerAdmin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.update', $order), [
                'status' => PurchaseOrder::STATUS_APPROVED,
                'admin_note' => 'Сам себе одобряю.',
            ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $order->id,
            'status' => PurchaseOrder::STATUS_NEW,
        ]);

        $this->actingAs($buyerAdmin)
            ->from(route('admin.orders.index'))
            ->patch(route('admin.orders.update', $order), [
                'status' => PurchaseOrder::STATUS_PAID,
                'admin_note' => 'Сам себе отмечаю оплату.',
            ])
            ->assertRedirect(route('admin.orders.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('artifacts', [
            'id' => $artifact->id,
            'status' => Artifact::STATUS_ON_SALE,
        ]);

        $this->actingAs($reviewAdmin)
            ->patch(route('admin.orders.update', $order), [
                'status' => PurchaseOrder::STATUS_APPROVED,
                'admin_note' => 'Одобрено другим администратором.',
            ])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $order->id,
            'status' => PurchaseOrder::STATUS_APPROVED,
        ]);

        $this->actingAs($reviewAdmin)
            ->patch(route('admin.orders.update', $order), [
                'status' => PurchaseOrder::STATUS_PAID,
                'admin_note' => 'Оплата подтверждена другим администратором.',
            ])
            ->assertRedirect(route('admin.orders.index'));

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $order->id,
            'status' => PurchaseOrder::STATUS_PAID,
        ]);
        $this->assertDatabaseHas('artifacts', [
            'id' => $artifact->id,
            'status' => Artifact::STATUS_SOLD,
        ]);
    }

    public function test_donation_submission_rejects_price(): void
    {
        $artifact = $this->seedCatalog();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('submissions.index'))
            ->post(route('submissions.store'), [
                'category_id' => $artifact->category_id,
                'title' => 'Дар с ценой',
                'owner_name' => $user->name,
                'contact_email' => $user->email,
                'desired_action' => ArtifactSubmission::ACTION_DONATE,
                'desired_price' => 1000,
                'description' => 'Цена при дарении запрещена.',
            ])
            ->assertRedirect(route('submissions.index'))
            ->assertSessionHasErrors('desired_price');

        $this->assertDatabaseMissing('artifact_submissions', [
            'title' => 'Дар с ценой',
        ]);
    }

    public function test_admin_accept_submission_uses_next_free_inventory_number(): void
    {
        $category = Category::query()->create([
            'name' => 'Скульптура',
            'description' => 'Объемные предметы фонда.',
        ]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        $year = now()->format('Y');

        foreach ([1, 2] as $number) {
            Artifact::query()->create([
                'category_id' => $category->id,
                'title' => 'Существующий предмет '.$number,
                'inventory_number' => 'MS-'.$year.'-'.str_pad((string) $number, 5, '0', STR_PAD_LEFT),
                'period' => 'XX век',
                'material' => 'Камень',
                'condition_state' => 'Хорошее',
                'acquisition_type' => 'storage',
                'status' => Artifact::STATUS_IN_STORAGE,
                'appraised_value' => 10000,
                'description' => 'Контрольный предмет.',
            ]);
        }

        $submission = ArtifactSubmission::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Древнегреческая статуя',
            'owner_name' => $user->name,
            'contact_email' => $user->email,
            'desired_action' => ArtifactSubmission::ACTION_SELL,
            'desired_price' => 15000,
            'description' => 'Нормальная такая древнегреческая статуя.',
            'provenance' => 'Нашел дома у барной стойки.',
            'status' => ArtifactSubmission::STATUS_NEW,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.submissions.update', $submission), [
                'category_id' => $category->id,
                'status' => ArtifactSubmission::STATUS_ACCEPTED,
                'admin_note' => 'Ок, готов выкупить.',
            ])
            ->assertRedirect(route('admin.submissions.index'));

        $this->assertDatabaseHas('artifacts', [
            'title' => 'Древнегреческая статуя',
            'inventory_number' => 'MS-'.$year.'-00003',
        ]);
        $this->assertDatabaseHas('artifact_submissions', [
            'id' => $submission->id,
            'status' => ArtifactSubmission::STATUS_ACCEPTED,
        ]);
    }

    public function test_admin_artifact_store_generates_inventory_number_and_update_cannot_change_it(): void
    {
        $artifact = $this->seedCatalog();
        $category = $artifact->category;
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $year = now()->format('Y');

        $this->actingAs($admin)
            ->post(route('admin.artifacts.store'), [
                'category_id' => $category->id,
                'title' => 'Медаль 2 мировой',
                'period' => 'Конец XIX века',
                'material' => 'Сталь',
                'condition_state' => 'Хорошее, есть мелкие сколы',
                'acquisition_type' => 'storage',
                'status' => Artifact::STATUS_IN_STORAGE,
                'appraised_value' => 10000,
                'sale_price' => null,
                'description' => 'Нормальная медаль.',
            ])
            ->assertRedirect(route('admin.artifacts.index'));

        $createdArtifact = Artifact::query()
            ->where('title', 'Медаль 2 мировой')
            ->firstOrFail();

        $this->assertSame('MS-'.$year.'-00001', $createdArtifact->inventory_number);

        $this->actingAs($admin)
            ->put(route('admin.artifacts.update', $createdArtifact), [
                'category_id' => $category->id,
                'title' => 'Медаль 2 мировой обновленная',
                'inventory_number' => 'MANUAL-EDIT-001',
                'period' => 'Конец XIX века',
                'material' => 'Сталь',
                'condition_state' => 'Хорошее',
                'acquisition_type' => 'storage',
                'status' => Artifact::STATUS_IN_STORAGE,
                'appraised_value' => 12000,
                'sale_price' => null,
                'description' => 'Номер менять нельзя.',
            ])
            ->assertRedirect(route('admin.artifacts.index'));

        $createdArtifact->refresh();

        $this->assertSame('MS-'.$year.'-00001', $createdArtifact->inventory_number);
        $this->assertSame('Медаль 2 мировой обновленная', $createdArtifact->title);
        $this->assertDatabaseMissing('artifacts', [
            'inventory_number' => 'MANUAL-EDIT-001',
        ]);
    }

    public function test_sanctum_api_auth_and_protected_endpoints(): void
    {
        $artifact = $this->seedCatalog();
        $user = User::factory()->create(['password' => 'password']);

        $token = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'user'])
            ->json('token');

        $this->getJson('/api/artifacts')->assertOk()->assertJsonStructure(['data']);

        $this->withToken($token)
            ->postJson("/api/artifacts/{$artifact->id}/orders", [
                'buyer_name' => $user->name,
                'buyer_email' => $user->email,
            ])
            ->assertCreated()
            ->assertJsonStructure(['data']);

        $this->withToken($token)->getJson('/api/orders')->assertOk()->assertJsonStructure(['data']);
        $this->withToken($token)->postJson('/api/logout')->assertOk();
    }

    public function test_admin_can_create_category_via_api_endpoint(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $adminToken = $admin->createToken('admin-category-test')->plainTextToken;

        $this->withToken($adminToken)
            ->postJson('/api/admin/categories', [
                'name' => 'Нумизматика',
                'description' => 'Монеты, медали и знаки.',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Категория добавлена.')
            ->assertJsonPath('data.name', 'Нумизматика');

        $this->assertDatabaseHas('categories', [
            'name' => 'Нумизматика',
            'description' => 'Монеты, медали и знаки.',
        ]);

        $this->withToken($adminToken)
            ->postJson('/api/admin/categories', [
                'name' => 'Нумизматика',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_regular_user_cannot_create_category_via_admin_api_endpoint(): void
    {
        $user = User::factory()->create();
        $userToken = $user->createToken('user-category-test')->plainTextToken;

        $this->withToken($userToken)
            ->postJson('/api/admin/categories', [
                'name' => 'Оружейный фонд',
                'description' => 'Предметы вооружения и защитного снаряжения.',
            ])
            ->assertForbidden();
    }

    public function test_blocked_user_is_rejected_by_api_middleware(): void
    {
        $artifact = $this->seedCatalog();
        $user = User::factory()->create(['blocked_at' => now()]);
        $token = $user->createToken('blocked-test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/submissions', [
                'category_id' => $artifact->category_id,
                'title' => 'API заблокирован',
                'owner_name' => $user->name,
                'contact_email' => $user->email,
                'desired_action' => ArtifactSubmission::ACTION_DONATE,
                'description' => 'Не должно сохраниться.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('artifact_submissions', [
            'title' => 'API заблокирован',
        ]);
    }

    public function test_validation_errors_are_rendered_next_to_fields(): void
    {
        $this->followingRedirects()
            ->from(route('register'))
            ->post(route('register.store'), [
                'name' => '',
                'email' => 'not-email',
                'password' => '123',
                'password_confirmation' => '456',
            ])
            ->assertOk()
            ->assertSee('Поле ФИО обязательно для заполнения.')
            ->assertSee('Поле email должно быть корректным email.')
            ->assertSee('class="is-invalid"', false)
            ->assertSee('field-error');
    }

    private function seedCatalog(): Artifact
    {
        $category = Category::query()->create([
            'name' => 'Керамика',
            'description' => 'Фарфор и фаянс.',
        ]);

        return Artifact::query()->create([
            'category_id' => $category->id,
            'title' => 'Фаянсовый чайный сервиз',
            'inventory_number' => 'MS-TEST-001',
            'period' => 'XIX век',
            'material' => 'Фаянс',
            'condition_state' => 'Хорошее',
            'acquisition_type' => 'purchase',
            'status' => Artifact::STATUS_ON_SALE,
            'appraised_value' => 84000,
            'sale_price' => 93000,
            'description' => 'Комплект доступен для выкупа.',
        ]);
    }
}
