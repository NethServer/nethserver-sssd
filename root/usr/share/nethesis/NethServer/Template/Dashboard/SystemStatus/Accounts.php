<?php

if($view['provider'] === 'none') {
    return;
}

echo "<div class='dashboard-item'>";
echo $view->header()->setAttribute('template',$T('accounts_title'));
echo "<dl>";
foreach ($view['accounts'] as $type => $n) {
    if(! $n) {
        continue;
    }
    echo "<dt>".$T($type. '_label')."</dt><dd>"; echo $n; echo "</dd>";
}
echo "</dl>";
echo "</div>";
