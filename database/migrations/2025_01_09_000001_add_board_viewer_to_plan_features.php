<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 모든 플랜의 features에서 main_widget_types와 custom_page_widget_types에 board_viewer 추가
        $plans = DB::table('plans')->get();
        
        foreach ($plans as $plan) {
            $features = json_decode($plan->features, true);
            
            if (is_array($features)) {
                // main_widget_types에 board_viewer 추가 (이미 있지 않은 경우)
                if (isset($features['main_widget_types']) && is_array($features['main_widget_types'])) {
                    if (!in_array('board_viewer', $features['main_widget_types'])) {
                        // board 다음에 board_viewer 추가
                        $index = array_search('board', $features['main_widget_types']);
                        if ($index !== false) {
                            array_splice($features['main_widget_types'], $index + 1, 0, 'board_viewer');
                        } else {
                            // board가 없으면 배열 끝에 추가
                            $features['main_widget_types'][] = 'board_viewer';
                        }
                    }
                }
                
                // custom_page_widget_types에 board_viewer 추가 (이미 있지 않은 경우)
                if (isset($features['custom_page_widget_types']) && is_array($features['custom_page_widget_types'])) {
                    if (!in_array('board_viewer', $features['custom_page_widget_types'])) {
                        // board 다음에 board_viewer 추가
                        $index = array_search('board', $features['custom_page_widget_types']);
                        if ($index !== false) {
                            array_splice($features['custom_page_widget_types'], $index + 1, 0, 'board_viewer');
                        } else {
                            // board가 없으면 배열 끝에 추가
                            $features['custom_page_widget_types'][] = 'board_viewer';
                        }
                    }
                }
                
                // 업데이트된 features 저장
                DB::table('plans')
                    ->where('id', $plan->id)
                    ->update(['features' => json_encode($features)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 롤백: board_viewer 제거
        $plans = DB::table('plans')->get();
        
        foreach ($plans as $plan) {
            $features = json_decode($plan->features, true);
            
            if (is_array($features)) {
                // main_widget_types에서 board_viewer 제거
                if (isset($features['main_widget_types']) && is_array($features['main_widget_types'])) {
                    $features['main_widget_types'] = array_values(array_filter($features['main_widget_types'], function($type) {
                        return $type !== 'board_viewer';
                    }));
                }
                
                // custom_page_widget_types에서 board_viewer 제거
                if (isset($features['custom_page_widget_types']) && is_array($features['custom_page_widget_types'])) {
                    $features['custom_page_widget_types'] = array_values(array_filter($features['custom_page_widget_types'], function($type) {
                        return $type !== 'board_viewer';
                    }));
                }
                
                // 업데이트된 features 저장
                DB::table('plans')
                    ->where('id', $plan->id)
                    ->update(['features' => json_encode($features)]);
            }
        }
    }
};

