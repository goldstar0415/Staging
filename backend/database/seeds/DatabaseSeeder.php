<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call(RolesSeeder::class);
        $this->call(SpotTypesTableSeeder::class);
        $this->call(SpotDefaultCategoriesTableSeeder::class);
        $this->call(SocialsTableSeeder::class);
        $this->call(ActivityLevelSeeder::class);
        $this->call(UserTableSeeder::class);
        if (App::environment('local')) {
            $upload_dir = public_path('upload/App');
            if (File::isDirectory($upload_dir)) {
                File::deleteDirectory($upload_dir);
            }
            $this->call(WallsTableSeeder::class);
            $this->call(AreasTableSeeder::class);
            $this->call(PlansTableSeeder::class);
            $this->call(ActivityCategoriesTableSeeder::class);
            $this->call(ActivitiesTableSeeder::class);
            $this->call(ChatMessagesTableSeeder::class);
            $this->call(FollowingsTableSeeder::class);

            $this->call(AlbumTableSeeder::class);
            $this->call(AlbumPhotosTableSeeder::class);
            $this->call(AlbumPhotoCommentsTableSeeder::class);

            $this->call(BlogCategoriesTableSeeder::class);
            $this->call(BlogsTableSeeder::class);
            $this->call(BlogCommentsTableSeeder::class);
            $this->call(BloggerRequestsTableSeeder::class);

            $this->call(SpotTypeCategoriesTableSeeder::class);
            $this->call(SpotTableSeeder::class);
            $this->call(SpotPhotoTableSeeder::class);
            $this->call(SpotPointsTableSeeder::class);
            $this->call(TagsTableSeeder::class);
            $this->call(SpotVotesTableSeeder::class);
            $this->call(SpotCommentsTableSeeder::class);

            $this->call(FavoritesTableSeeder::class);
        }

        Model::reguard();
    }
}
