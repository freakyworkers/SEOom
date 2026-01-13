<?php
// 서버에서 실행할 스크립트: Site.php 파일의 customPageWidgetCount 부분 수정

$filePath = '/var/www/seoom/app/Models/Site.php';
$content = file_get_contents($filePath);

// 746-749 라인 수정
$oldPattern1 = '/\$customPageWidgetCount = \\\\App\\\\Models\\\\CustomPageWidget::where\([\'"]site_id[\'"], \$this->id\)->count\(\);/';
$newPattern1 = "\$customPageWidgetCount = \\\\App\\\\Models\\\\CustomPageWidget::whereHas('customPage', function(\$query) {\n            \$query->where('site_id', \$this->id);\n        })->count();";

$content = preg_replace($oldPattern1, $newPattern1, $content);

// 794-797 라인 수정 (같은 패턴)
$content = preg_replace($oldPattern1, $newPattern1, $content);

// 829-831 라인 수정
$oldPattern2 = '/ \+ \\\\App\\\\Models\\\\CustomPageWidget::where\([\'"]site_id[\'"], \$this->id\)->count\(\),/';
$newPattern2 = " + \\\\App\\\\Models\\\\CustomPageWidget::whereHas('customPage', function(\$query) {\n                               \$query->where('site_id', \$this->id);\n                           })->count(),";

$content = preg_replace($oldPattern2, $newPattern2, $content);

file_put_contents($filePath, $content);
echo "Site.php 파일이 수정되었습니다.\n";

