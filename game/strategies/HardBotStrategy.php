<?php

namespace Game\Helpers;

class GameRulesHelper
{
    public static function checkWin(array $board, string $symbol, int $size): bool
    {
        // Check rows
        for ($i = 0; $i < $size; $i++) {
            if (count(array_unique($board[$i])) === 1 && $board[$i][0] === $symbol) return true;
        }

        // Check columns
        for ($i = 0; $i < $size; $i++) {
            if (count(array_unique(array_column($board, $i))) === 1 && $board[0][$i] === $symbol) return true;
        }

        // Check diagonals
        return self::checkDiagonals($board, $symbol, $size);
    }

    private static function checkDiagonals(array $board, string $symbol, int $size): bool
    {
        $diag1 = $diag2 = true;
        for ($i = 0; $i < $size; $i++) {
            if ($board[$i][$i] !== $symbol) $diag1 = false;
            if ($board[$i][$size - $i - 1] !== $symbol) $diag2 = false;
        }
        return $diag1 || $diag2;
    }

    public static function checkDraw(array $board): bool
    {
        foreach ($board as $row) {
            if (in_array('', $row)) return false;
        }
        return true;
    }

    public static function getAvailableMoves(array $board): array
    {
        $moves = [];
        foreach ($board as $i => $row) {
            foreach ($row as $j => $cell) {
                if ($cell === '') {
                    $moves[] = [$i, $j];
                }
            }
        }
        return $moves;
    }
}