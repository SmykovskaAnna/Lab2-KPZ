<?php

namespace Core\Interfaces;

interface BotStrategyInterface
{
    /**
     * Робить хід для бота на ігровій дошці.
     *
     * @param array $board Поточна ігрова дошка.
     * @param string $botSymbol Символ бота.
     * @param string $playerSymbol Символ гравця (для AI, що блокує).
     * @param int $boardSize Розмір дошки.
     * @return array|null Координати ходу [row, col] або null, якщо ходів немає.
     */
    public function makeMove(array $board, string $botSymbol, string $playerSymbol, int $boardSize): ?array;
}