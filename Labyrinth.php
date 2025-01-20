<?php
class Labyrinth {
    protected int $height;
    protected int $width;
    protected array $start = [];
    protected array $end = [];
    protected array $labyrinth = [];
    protected array $route = [];

    protected const MOVE_DIRECTIONS = [[-1, 0], [1, 0], [0, -1], [0, 1]];

    public function run(): void {
        try {
            $this->input();
            $this->findPathBfs();
            $this->output();
        } catch (Exception $exception) {
            fwrite(STDERR, $exception->getMessage());
        }
    }

    protected function input(): void {
        $input = fgets(STDIN);
        if ($input === false) {
            throw new Exception("Invalid input: unable to read labyrinth size.");
        }
        $values = explode(' ', trim($input));
        if (count($values) !== 2) {
            throw new Exception("Invalid input: labyrinth size must consist of two numbers.");
        }
        $this->height = (int)$values[0];
        $this->width = (int)$values[1];

        if ($this->height <= 0 || $this->width <= 0) {
            throw new Exception("Invalid labyrinth size: height and width must be positive.");
        }

        for ($i = 0; $i < $this->height; $i++) {
            $input = fgets(STDIN);
            if ($input === false) {
                throw new Exception("Invalid input: unable to read labyrinth row.");
            }
            $row = array_map('intval', explode(' ', trim($input)));
            if (count($row) !== $this->width) {
                throw new Exception("Invalid input: row length does not match labyrinth width.");
            }
            $this->labyrinth[] = $row;
        }

        $input = fgets(STDIN);
        if ($input === false) {
            throw new Exception("Invalid input: unable to read start and end positions.");
        }
        $values = array_map('intval', explode(' ', trim($input)));
        if (count($values) !== 4) {
            throw new Exception("Invalid input: start and end positions must consist of four numbers.");
        }
        $this->start = [$values[0], $values[1]];
        $this->end = [$values[2], $values[3]];

        if ($this->start[0] < 0 || $this->start[0] >= $this->height || $this->start[1] < 0 || $this->start[1] >= $this->width) {
            throw new Exception("Invalid start position: out of labyrinth bounds.");
        }
        if ($this->end[0] < 0 || $this->end[0] >= $this->height || $this->end[1] < 0 || $this->end[1] >= $this->width) {
            throw new Exception("Invalid end position: out of labyrinth bounds.");
        }

        if ($this->labyrinth[$this->start[0]][$this->start[1]] === 0) {
            throw new Exception("Invalid start position: cannot start on a wall.");
        }
        if ($this->labyrinth[$this->end[0]][$this->end[1]] === 0) {
            throw new Exception("Invalid end position: cannot end on a wall.");
        }
    }

    protected function output(): void {
        if (!empty($this->route)) {
            foreach ($this->route as $coord) {
                echo $coord[0] . ' ' . $coord[1] . "\n";
            }
            echo ".\n";
        } else {
            fwrite(STDERR, "No path found.\n");
        }
    }

    protected function findPathBfs(): void {
        $queue = new SplQueue();
        $queue->enqueue([$this->start, [$this->start]]);
        $visited = array_fill(0, $this->height, array_fill(0, $this->width, false));
        $visited[$this->start[0]][$this->start[1]] = true;

        while (!$queue->isEmpty()) {
            [$current, $path] = $queue->dequeue();
            if ($current === $this->end) {
                $this->route = $path;
                return;
            }

            foreach (self::MOVE_DIRECTIONS as $direction) {
                $nextRow = $current[0] + $direction[0];
                $nextCol = $current[1] + $direction[1];

                if ($nextRow >= 0 && $nextRow < $this->height && $nextCol >= 0 && $nextCol < $this->width) {
                    if ($this->labyrinth[$nextRow][$nextCol] !== 0 && !$visited[$nextRow][$nextCol]) {
                        $visited[$nextRow][$nextCol] = true;
                        $newPath = $path;
                        $newPath[] = [$nextRow, $nextCol];
                        $queue->enqueue([[$nextRow, $nextCol], $newPath]);
                    }
                }
            }
        }

        $this->route = [];
    }
}