<?php

namespace Database\Seeders;

use App\Models\Artifact;
use App\Models\ArtifactSubmission;
use App\Models\Category;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Администратор музея',
            'email' => 'admin@museum.test',
            'password' => 'password',
            'role' => User::ROLE_ADMIN,
        ]);

        $user = User::query()->create([
            'name' => 'Иван Коллекционер',
            'email' => 'user@museum.test',
            'password' => 'password',
            'role' => User::ROLE_USER,
        ]);

        $paintings = Category::query()->create([
            'name' => 'Живопись',
            'description' => 'Картины, этюды и графика из частных и музейных собраний.',
        ]);

        $ceramics = Category::query()->create([
            'name' => 'Керамика',
            'description' => 'Фарфор, фаянс и бытовая керамика разных периодов.',
        ]);

        $documents = Category::query()->create([
            'name' => 'Архивные документы',
            'description' => 'Письма, фотографии, афиши и рукописные материалы.',
        ]);

        $samovar = Artifact::query()->create([
            'category_id' => $ceramics->id,
            'owner_id' => $user->id,
            'title' => 'Фаянсовый чайный сервиз',
            'inventory_number' => 'MS-2026-00001',
            'period' => 'Конец XIX века',
            'material' => 'Фаянс, роспись',
            'condition_state' => 'Хорошее, есть мелкие сколы',
            'acquisition_type' => 'purchase',
            'status' => Artifact::STATUS_ON_SALE,
            'appraised_value' => 84000,
            'sale_price' => 93000,
            'description' => 'Комплект из шести предметов, передан на временное хранение с возможностью выкупа.',
        ]);

        Artifact::query()->create([
            'category_id' => $paintings->id,
            'owner_id' => null,
            'title' => 'Этюд городского бульвара',
            'inventory_number' => 'MS-2026-00002',
            'period' => '1920-е годы',
            'material' => 'Картон, масло',
            'condition_state' => 'Требуется профилактическая реставрация',
            'acquisition_type' => 'donation',
            'status' => Artifact::STATUS_RESTORATION,
            'appraised_value' => 120000,
            'sale_price' => null,
            'description' => 'Работа поступила по договору дарения и проходит первичную реставрационную оценку.',
        ]);

        Artifact::query()->create([
            'category_id' => $documents->id,
            'owner_id' => null,
            'title' => 'Афиша благотворительного вечера',
            'inventory_number' => 'MS-2026-00003',
            'period' => '1913 год',
            'material' => 'Бумага, типографская печать',
            'condition_state' => 'Удовлетворительное',
            'acquisition_type' => 'storage',
            'status' => Artifact::STATUS_IN_STORAGE,
            'appraised_value' => 26000,
            'sale_price' => null,
            'description' => 'Редкая городская афиша из частного архива.',
        ]);

        ArtifactSubmission::query()->create([
            'user_id' => $user->id,
            'category_id' => $documents->id,
            'title' => 'Семейная переписка врача земской больницы',
            'owner_name' => 'Иван Коллекционер',
            'contact_email' => 'user@museum.test',
            'contact_phone' => '+7 900 000-00-00',
            'desired_action' => ArtifactSubmission::ACTION_DONATE,
            'desired_price' => null,
            'description' => 'Пачка писем и открыток, найденных в семейном архиве.',
            'provenance' => 'Хранилось у наследников семьи с 1940-х годов.',
            'status' => ArtifactSubmission::STATUS_NEW,
        ]);

        PurchaseOrder::query()->create([
            'user_id' => $user->id,
            'artifact_id' => $samovar->id,
            'buyer_name' => 'Иван Коллекционер',
            'buyer_email' => 'user@museum.test',
            'buyer_phone' => '+7 900 000-00-00',
            'offered_price' => $samovar->sale_price,
            'status' => PurchaseOrder::STATUS_NEW,
            'comment' => 'Готов обсудить условия выкупа и самовывоз.',
        ]);
    }
}
