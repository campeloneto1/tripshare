<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para Posts
        Schema::table('posts', function (Blueprint $table) {
            $table->index('trip_id', 'idx_posts_trip_id');
            $table->index(['user_id', 'created_at'], 'idx_posts_user_created');
            $table->index('shared_post_id', 'idx_posts_shared_post');
            $table->index('created_at', 'idx_posts_created_at');
        });

        // Índices para Post Comments
        Schema::table('posts_comments', function (Blueprint $table) {
            $table->index('post_id', 'idx_post_comments_post_id');
            $table->index('parent_id', 'idx_post_comments_parent_id');
            $table->index(['post_id', 'created_at'], 'idx_post_comments_post_created');
        });

        // Índices para Post Likes
        Schema::table('posts_likes', function (Blueprint $table) {
            $table->index('post_id', 'idx_post_likes_post_id');
            $table->index(['user_id', 'post_id'], 'idx_post_likes_user_post');
        });

        // Índices para Uploads (polimórfico)
        Schema::table('uploads', function (Blueprint $table) {
            $table->index(['uploadable_type', 'uploadable_id'], 'idx_uploads_morphs');
            $table->index('order', 'idx_uploads_order');
            $table->index('is_main', 'idx_uploads_is_main');
        });

        // Índices para Trips
        Schema::table('trips', function (Blueprint $table) {
            $table->index('user_id', 'idx_trips_user_id');
            $table->index('is_public', 'idx_trips_is_public');
            $table->index(['user_id', 'created_at'], 'idx_trips_user_created');
            $table->index('start_date', 'idx_trips_start_date');
        });

        // Índices para Trip Users (pivot)
        Schema::table('trips_users', function (Blueprint $table) {
            $table->index('trip_id', 'idx_trips_users_trip_id');
            $table->index('user_id', 'idx_trips_users_user_id');
            $table->index(['trip_id', 'user_id'], 'idx_trips_users_trip_user');
            $table->index('role', 'idx_trips_users_role');
        });

        // Índices para User Follows
        Schema::table('users_follows', function (Blueprint $table) {
            $table->index('follower_id', 'idx_user_follows_follower');
            $table->index('following_id', 'idx_user_follows_following');
            $table->index('status', 'idx_user_follows_status');
            $table->index(['follower_id', 'following_id'], 'idx_user_follows_pair');
        });

        // Índices para Trip Days
        Schema::table('trips_days', function (Blueprint $table) {
            $table->index('trip_id', 'idx_trip_days_trip_id');
            $table->index(['trip_id', 'date'], 'idx_trip_days_trip_date');
        });

        // Índices para Trip Day Cities
        Schema::table('trips_days_cities', function (Blueprint $table) {
            $table->index('trip_day_id', 'idx_trip_cities_day_id');
        });

        // Índices para Trip Day Events
        Schema::table('trips_days_events', function (Blueprint $table) {
            $table->index('trip_day_city_id', 'idx_trip_events_city_id');
            $table->index('type', 'idx_trip_events_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Posts
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('idx_posts_trip_id');
            $table->dropIndex('idx_posts_user_created');
            $table->dropIndex('idx_posts_shared_post');
            $table->dropIndex('idx_posts_created_at');
        });

        // Post Comments
        Schema::table('posts_comments', function (Blueprint $table) {
            $table->dropIndex('idx_post_comments_post_id');
            $table->dropIndex('idx_post_comments_parent_id');
            $table->dropIndex('idx_post_comments_post_created');
        });

        // Post Likes
        Schema::table('posts_likes', function (Blueprint $table) {
            $table->dropIndex('idx_post_likes_post_id');
            $table->dropIndex('idx_post_likes_user_post');
        });

        // Uploads
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropIndex('idx_uploads_morphs');
            $table->dropIndex('idx_uploads_order');
            $table->dropIndex('idx_uploads_is_main');
        });

        // Trips
        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex('idx_trips_user_id');
            $table->dropIndex('idx_trips_is_public');
            $table->dropIndex('idx_trips_user_created');
            $table->dropIndex('idx_trips_start_date');
        });

        // Trip Users
        Schema::table('trips_users', function (Blueprint $table) {
            $table->dropIndex('idx_trips_users_trip_id');
            $table->dropIndex('idx_trips_users_user_id');
            $table->dropIndex('idx_trips_users_trip_user');
            $table->dropIndex('idx_trips_users_role');
        });

        // User Follows
        Schema::table('users_follows', function (Blueprint $table) {
            $table->dropIndex('idx_user_follows_follower');
            $table->dropIndex('idx_user_follows_following');
            $table->dropIndex('idx_user_follows_status');
            $table->dropIndex('idx_user_follows_pair');
        });

        // Trip Days
        Schema::table('trips_days', function (Blueprint $table) {
            $table->dropIndex('idx_trip_days_trip_id');
            $table->dropIndex('idx_trip_days_trip_date');
        });

        // Trip Day Cities
        Schema::table('trips_days_cities', function (Blueprint $table) {
            $table->dropIndex('idx_trip_cities_day_id');
        });

        // Trip Day Events
        Schema::table('trips_days_events', function (Blueprint $table) {
            $table->dropIndex('idx_trip_events_city_id');
            $table->dropIndex('idx_trip_events_type');
        });
    }
};
