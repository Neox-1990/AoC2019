<?php

$pstart = microtime(true);

$input = file_get_contents('input.txt');
$input = explode("\r\n", $input);
$path1 = explode(',', $input[0]);
$path2 = explode(',', $input[1]);

//Convert to Step-Objects
$path1 = array_map(function ($step) {
    return new Step($step);
}, $path1);
$path2 = array_map(function ($step) {
    return new Step($step);
}, $path2);

//convert Steps into Path with the help of Lines
$temp_path = [];
$p1 = new Point(0, 0);
foreach ($path1 as $key => $step) {
    $p2 = doStep($p1, $step);
    $line = new Line($p1, $p2);
    $path_factory = new Path_Factory();
    $temp_path[] = $path_factory->getPath($line, $key);
    $p1 = $p2;
}

//Split path into horizontal and vertical
$path1_horizontal = array_filter($temp_path, function (Path $path) {
    return $path->getOrientation() == Path::HORIZONTAL;
});
$path1_vertical = array_filter($temp_path, function (Path $path) {
    return $path->getOrientation() == Path::VERTICAL;
});

//same for second path
$temp_path = [];
$p1 = new Point(0, 0);
foreach ($path2 as $key => $step) {
    $p2 = doStep($p1, $step);
    $line = new Line($p1, $p2);
    $path_factory = new Path_Factory();
    $temp_path[] = $path_factory->getPath($line, $key);
    $p1 = $p2;
}

$path2_horizontal = array_filter($temp_path, function (Path $path) {
    return $path->getOrientation() == Path::HORIZONTAL;
});
$path2_vertical = array_filter($temp_path, function (Path $path) {
    return $path->getOrientation() == Path::VERTICAL;
});


$intersections = [];
//Checking for intersections of the horizontal paths of path1 with vertical paths of path2
foreach ($path1_horizontal as $h_path) {
    $to_check = array_filter($path2_vertical, function (V_Path $v_path) use ($h_path) {
        return checkForIntersect($h_path, $v_path);
    });

    //getting the intersectionpoints and save the path-objects that intersect
    foreach ($to_check as $v_path) {
        $intersections[] = [
          'point' => new Point($v_path->getX(), $h_path->getY()),
          'h_path' => $h_path,
          'v_path' => $v_path,
          'h_path_owner' => 1
        ];
    }
}
//Checking for intersections of the vertical paths of path1 with horizontal paths of path2, analogus
foreach ($path1_vertical as $v_path) {
    $to_check = array_filter($path2_horizontal, function (H_Path $h_path) use ($v_path) {
        return checkForIntersect($h_path, $v_path);
    });

    foreach ($to_check as $h_path) {
        $intersections[] = [
        'point' => new Point($v_path->getX(), $h_path->getY()),
        'h_path' => $h_path,
        'v_path' => $v_path,
        'h_path_owner' => 2
      ];
    }
}

$intersections = array_map(function (array $intersection) use ($path1,$path2) {
    if ($intersection['h_path_owner'] == 1) {
        $steps1 = 0;
        //Steps for path1 until last point before intersection
        for ($i=0; $i < $intersection['h_path']->getIndex(); $i++) {
            $steps1 += $path1[$i]->getDistance();
        }
        //last bit until intersection
        if ($path1[$intersection['h_path']->getIndex()]->getDirection() == 'R') {
            $steps1 += $intersection['point']->getX() - $intersection['h_path']->getLowerX();
        } else {
            $steps1 += $intersection['h_path']->getUpperX() - $intersection['point']->getX();
        }

        $steps2 = 0;
        //Steps for path2 until last point before intersection
        for ($i=0; $i < $intersection['v_path']->getIndex(); $i++) {
            $steps2 += $path2[$i]->getDistance();
        }
        //last bit until intersection
        if ($path2[$intersection['v_path']->getIndex()]->getDirection() == 'U') {
            $steps2 += $intersection['point']->getY() - $intersection['v_path']->getLowerY();
        } else {
            $steps2 += $intersection['v_path']->getUpperY() - $intersection['point']->getY();
        }

        $steps = $steps1 + $steps2;
    } else {
        $steps1 = 0;
        //Steps for path2 until last point before intersection
        for ($i=0; $i < $intersection['h_path']->getIndex(); $i++) {
            $steps1 += $path2[$i]->getDistance();
        }
        //last bit until intersection
        if ($path2[$intersection['h_path']->getIndex()]->getDirection() == 'R') {
            $steps1 += $intersection['point']->getX() - $intersection['h_path']->getLowerX();
        } else {
            $steps1 += $intersection['h_path']->getUpperX() - $intersection['point']->getX();
        }

        $steps2 = 0;
        //Steps for path1 until last point before intersection
        for ($i=0; $i < $intersection['v_path']->getIndex(); $i++) {
            $steps2 += $path1[$i]->getDistance();
        }
        //last bit until intersection
        if ($path1[$intersection['v_path']->getIndex()]->getDirection() == 'U') {
            $steps2 += $intersection['point']->getY() - $intersection['v_path']->getLowerY();
        } else {
            $steps2 += $intersection['v_path']->getUpperY() - $intersection['point']->getY();
        }

        $steps = $steps1 + $steps2;
    }

    return [
      'point' => $intersection['point'],
      'distance' => abs($intersection['point']->getX()) + abs($intersection['point']->getY()),
      'steps' => $steps
    ];
}, $intersections);

//sort for shortest distance
usort($intersections, function ($a, $b) {
    return $a['distance'] > $b['distance'];
});
$pend1 = microtime(true);
echo "Closest intersection is at X:".$intersections[0]['point']->getX()." Y:".$intersections[0]['point']->getY()." with a manhatten distance of ".$intersections[0]['distance']."\r\n";

//sort for earliest intersection
usort($intersections, function ($a, $b) {
    return $a['steps'] > $b['steps'];
});
$pend2 = microtime(true);
echo "Earliest intersection is at X:".$intersections[0]['point']->getX()." Y:".$intersections[0]['point']->getY()." after ".$intersections[0]['steps']." Steps\r\n";

$ptime1 = round(($pend1 - $pstart)*100, 3);
$ptime2 = round(($pend2 - $pstart)*100, 3);
echo "\r\nPerfomance: ".$ptime1."ms (".$ptime2." ms)\r\n";

//Debugging

file_put_contents(
    'debug.txt',
    print_r(
        compact('intersections'),
        true
    )
);

//Scaffolding
class Step
{
    private $direction;
    private $distance;

    public function __construct(string $step)
    {
        $direction = substr($step, 0, 1);
        $distance = substr_replace($step, '', 0, 1);

        $this->direction = strval($direction);
        $this->distance = intval($distance);
    }

    public function setDirection(string $direction):void
    {
        $this->direction = $direction;
    }
    public function getDirection():string
    {
        return $this->direction;
    }

    public function setDistance(int $distance):void
    {
        $this->distance = $distance;
    }
    public function getDistance():int
    {
        return $this->distance;
    }
}


abstract class Path
{
    const HORIZONTAL = 'H';
    const VERTICAL = 'V';
    protected $orientation;
    protected $index; //index of the step that creates the path

    public function getOrientation():string
    {
        return $this->orientation;
    }
    public function setOrientation(string $orientation):void
    {
        $this->orientation = $orientation;
    }

    public function getIndex():int
    {
        return $this->index;
    }
    public function setIndex(int $index):void
    {
        $this->index = $index;
    }
}

class H_Path extends Path
{
    private $y;
    private $lower_x;
    private $upper_x;

    public function getY():int
    {
        return $this->y;
    }
    public function setY(int $y):void
    {
        $this->y = $y;
    }

    public function getLowerX():int
    {
        return $this->lower_x;
    }
    public function setLowerX(int $x):void
    {
        $this->lower_x = $x;
    }

    public function getUpperX():int
    {
        return $this->upper_x;
    }
    public function setUpperX(int $x):void
    {
        $this->upper_x = $x;
    }
}

class V_Path extends Path
{
    private $x;
    private $lower_y;
    private $upper_y;

    public function getX():int
    {
        return $this->x;
    }
    public function setX(int $x):void
    {
        $this->x = $x;
    }

    public function getLowerY():int
    {
        return $this->lower_y;
    }
    public function setLowerY(int $y):void
    {
        $this->lower_y = $y;
    }

    public function getUpperY():int
    {
        return $this->upper_y;
    }
    public function setUpperY(int $y):void
    {
        $this->upper_y = $y;
    }
}

class Path_Factory
{
    public function getPath(Line $line, int $index)
    {
        if ($line->isHorizontal()) {
            $path = new H_Path();
            $path->setOrientation(Path::HORIZONTAL);
            $path->setIndex($index);
            $path->setY($line->getP1()->getY());
            $path->setLowerX(min($line->getP1()->getX(), $line->getP2()->getX()));
            $path->setUpperX(max($line->getP1()->getX(), $line->getP2()->getX()));
            return $path;
        } else {
            $path = new V_Path();
            $path->setOrientation(Path::VERTICAL);
            $path->setIndex($index);
            $path->setX($line->getP1()->getX());
            $path->setLowerY(min($line->getP1()->getY(), $line->getP2()->getY()));
            $path->setUpperY(max($line->getP1()->getY(), $line->getP2()->getY()));
            return $path;
        }
    }
}

class Point
{
    private $x;
    private $y;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX():int
    {
        return $this->x;
    }
    public function setX(int $x):void
    {
        $this->x = $x;
    }

    public function getY():int
    {
        return $this->y;
    }
    public function setY(int $y):void
    {
        $this->y = $y;
    }
}

class Line
{
    private $p1;
    private $p2;

    public function __construct(Point $p1, Point $p2)
    {
        $this->p1 = $p1;
        $this->p2 = $p2;
    }

    public function getP1():Point
    {
        return $this->p1;
    }
    public function setP1(Point $p1):void
    {
        $this->p1 = $p1;
    }

    public function getP2():Point
    {
        return $this->p2;
    }
    public function setP2(Point $p2):void
    {
        $this->p2 = $p2;
    }

    public function isHorizontal():bool
    {
        return $this->p1->getY() == $this->p2->getY();
    }
}

/**
 * returns Point after doing a step from a given point
 * @param Point $point coordinate of "from-point"
 * @param Step $step the step to do
 * @return array Coordinates after the step
 */
function doStep(Point $point, Step $step):Point
{
    $point = clone($point);
    switch ($step->getDirection()) {
    case 'U':
      $point->setY($point->getY() + $step->getDistance());
      break;
    case 'R':
      $point->setX($point->getX() + $step->getDistance());
      break;
    case 'D':
      $point->setY($point->getY() - $step->getDistance());
      break;
    case 'L':
      $point->setX($point->getX() - $step->getDistance());
  }

    return $point;
}

function checkForIntersect(H_PATH $h_path, V_PATH $v_path):bool
{
    return  $v_path->getLowerY() < $h_path->getY() &&
          $v_path->getUpperY() > $h_path->getY() &&
          $h_path->getLowerX() < $v_path->getX() &&
          $h_path->getUpperX() > $v_path->getX();
}
