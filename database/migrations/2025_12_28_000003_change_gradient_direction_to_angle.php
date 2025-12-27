<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // main_widget_containers 테이블 수정
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->integer('background_gradient_angle')->nullable()->after('background_gradient_end');
        });
        
        // 기존 direction 값을 angle로 변환
        DB::table('main_widget_containers')->whereNotNull('background_gradient_direction')->get()->each(function ($container) {
            $direction = $container->background_gradient_direction;
            $angle = 90; // 기본값
            
            if ($direction === 'to right') {
                $angle = 0;
            } elseif ($direction === 'to bottom') {
                $angle = 90;
            } elseif ($direction === 'to left') {
                $angle = 180;
            } elseif ($direction === 'to top') {
                $angle = 270;
            } elseif ($direction === '45deg') {
                $angle = 45;
            } elseif ($direction === '135deg') {
                $angle = 135;
            }
            
            DB::table('main_widget_containers')
                ->where('id', $container->id)
                ->update(['background_gradient_angle' => $angle]);
        });
        
        // direction 컬럼 제거
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn('background_gradient_direction');
        });
        
        // custom_page_widget_containers 테이블 수정
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->integer('background_gradient_angle')->nullable()->after('background_gradient_end');
        });
        
        // 기존 direction 값을 angle로 변환
        DB::table('custom_page_widget_containers')->whereNotNull('background_gradient_direction')->get()->each(function ($container) {
            $direction = $container->background_gradient_direction;
            $angle = 90; // 기본값
            
            if ($direction === 'to right') {
                $angle = 0;
            } elseif ($direction === 'to bottom') {
                $angle = 90;
            } elseif ($direction === 'to left') {
                $angle = 180;
            } elseif ($direction === 'to top') {
                $angle = 270;
            } elseif ($direction === '45deg') {
                $angle = 45;
            } elseif ($direction === '135deg') {
                $angle = 135;
            }
            
            DB::table('custom_page_widget_containers')
                ->where('id', $container->id)
                ->update(['background_gradient_angle' => $angle]);
        });
        
        // direction 컬럼 제거
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->dropColumn('background_gradient_direction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // main_widget_containers 테이블 복원
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->string('background_gradient_direction')->nullable()->after('background_gradient_end');
        });
        
        // angle 값을 direction으로 변환
        DB::table('main_widget_containers')->whereNotNull('background_gradient_angle')->get()->each(function ($container) {
            $angle = $container->background_gradient_angle;
            $direction = 'to right'; // 기본값
            
            if ($angle == 0) {
                $direction = 'to right';
            } elseif ($angle == 90) {
                $direction = 'to bottom';
            } elseif ($angle == 180) {
                $direction = 'to left';
            } elseif ($angle == 270) {
                $direction = 'to top';
            } elseif ($angle == 45) {
                $direction = '45deg';
            } elseif ($angle == 135) {
                $direction = '135deg';
            }
            
            DB::table('main_widget_containers')
                ->where('id', $container->id)
                ->update(['background_gradient_direction' => $direction]);
        });
        
        Schema::table('main_widget_containers', function (Blueprint $table) {
            $table->dropColumn('background_gradient_angle');
        });
        
        // custom_page_widget_containers 테이블 복원
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->string('background_gradient_direction')->nullable()->after('background_gradient_end');
        });
        
        // angle 값을 direction으로 변환
        DB::table('custom_page_widget_containers')->whereNotNull('background_gradient_angle')->get()->each(function ($container) {
            $angle = $container->background_gradient_angle;
            $direction = 'to right'; // 기본값
            
            if ($angle == 0) {
                $direction = 'to right';
            } elseif ($angle == 90) {
                $direction = 'to bottom';
            } elseif ($angle == 180) {
                $direction = 'to left';
            } elseif ($angle == 270) {
                $direction = 'to top';
            } elseif ($angle == 45) {
                $direction = '45deg';
            } elseif ($angle == 135) {
                $direction = '135deg';
            }
            
            DB::table('custom_page_widget_containers')
                ->where('id', $container->id)
                ->update(['background_gradient_direction' => $direction]);
        });
        
        Schema::table('custom_page_widget_containers', function (Blueprint $table) {
            $table->dropColumn('background_gradient_angle');
        });
    }
};

