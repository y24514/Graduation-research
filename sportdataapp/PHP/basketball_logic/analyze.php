<?php
function analyzeQuarter(array $game, int $quarter): array {
    $res = [];
    foreach(['A','B'] as $t) {
        $res[$t] = ['points' => 0, 'players' => []];
    }

    foreach ($game['actions'] as $a) {
        // 1. 指定のクォーター以外はスキップ
        if ($a['q'] != $quarter) continue;
        
        // ★修正点：交代データ(sub)には 'player' キーがないのでスキップする
        if ($a['type'] === 'sub') continue; 

        $team = $a['team']; 
        
        // 念のため、playerキーが存在するか確認
        if (!isset($a['player'])) continue;
        $pid = $a['player'];

        if (!isset($res[$team]['players'][$pid])) {
            $res[$team]['players'][$pid] = [
                'fg2_a'=>0, 'fg2_m'=>0, 
                'fg3_a'=>0, 'fg3_m'=>0, 
                'ft_a'=>0, 'ft_m'=>0, 
                'pts'=>0, 'foul'=>0, 'to'=>0
            ];
        }

        if ($a['type'] === 'shot') {
            $pt = (int)$a['point']; 
            $isSuccess = ($a['result'] === 'success');
            if ($pt === 1) { 
                $res[$team]['players'][$pid]['ft_a']++; 
                if ($isSuccess) $res[$team]['players'][$pid]['ft_m']++; 
            }
            elseif ($pt === 2) { 
                $res[$team]['players'][$pid]['fg2_a']++; 
                if ($isSuccess) $res[$team]['players'][$pid]['fg2_m']++; 
            }
            elseif ($pt === 3) { 
                $res[$team]['players'][$pid]['fg3_a']++; 
                if ($isSuccess) $res[$team]['players'][$pid]['fg3_m']++; 
            }
            if ($isSuccess) { 
                $res[$team]['players'][$pid]['pts'] += $pt; 
                $res[$team]['points'] += $pt; 
            }
        } elseif ($a['type'] === 'foul') {
            $res[$team]['players'][$pid]['foul']++;
        } elseif ($a['type'] === 'to') {
            $res[$team]['players'][$pid]['to']++;
        }
    }
    return $res;
}