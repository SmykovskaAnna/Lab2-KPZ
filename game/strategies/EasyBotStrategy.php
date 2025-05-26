<?php

namespace Game\Strategies;

use Core\Interfaces\BotStrategyInterface;

class EasyBotStrategy implements BotStrategyInterface
{
    public function makeMove(array $board, string $botSymbol, string $playerSymbol, int $boardSize): ?array
    {
        // Простий AI: робить перший доступний хід
        foreach ($board as $i => $row) {
            foreach ($row as $j => $cell) {
                if ($cell === '') {
                    return [$i, $j];
                }
            }
        }
        return null;
    }
}