--TEST--
PHP Spec test generated from ./classes/dynamic_properties.php
--FILE--
<?php

/*
   +-------------------------------------------------------------+
   | Copyright (c) 2014 Facebook, Inc. (http://www.facebook.com) |
   +-------------------------------------------------------------+
*/

error_reporting(-1);

class Point
{
    private $x;
    private $y;
    private $dynamicProperties = array();

    public $dummy = -100;   // for test purposes only

    public function __construct($x = 0, $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function __set($name, $value)
    {
//          echo __METHOD__ . "($name, $value)\n";
        echo __METHOD__ . "($name, xx)\n"; // used if $value can't be converted to string

        $this->dynamicProperties[$name] = $value;
    }

    public function __get($name)
    {
        echo __METHOD__ . "($name)\n";

        if (array_key_exists($name, $this->dynamicProperties))
        {
            return $this->dynamicProperties[$name];
        }

        // no-such-property error handling goes here
        return null;
    }

    public function __isset($name)
    {
        echo __METHOD__ . "($name)\n";

        return isset($this->dynamicProperties[$name]);
    }

    public function __unset($name)
    {
        echo __METHOD__ . "($name)\n";

        unset($this->dynamicProperties[$name]);
    }
}

$p = new Point(5, 9);

echo "----------------------\n";

$v = $p->dummy;             // get visible property
var_dump($v);
$v = $p->DUMmy;             // this is not the same as "dummy"
var_dump($v);
echo "dummy: $v\n";
$v = $p->__get('dummy');    // get dynamic property, if one exists; else, fails
echo "dynamic dummy: $v\n";

$p->dummy = 987;            // set visible property
$p->__set('dummy', 456);    // set dynamic property
//$p->__set('DUMmy', 456);    // case is sensitive

$v = $p->dummy;             // get visible property
echo "dummy: $v\n";
$v = $p->__get('dummy');    // get dynamic property
echo "dynamic dummy: $v\n";

echo "----------------------\n";

var_dump(isset($p->dummy));     // test if dummy exists and is accessible, or is dynamic
var_dump($p->__isset('dummy')); // test if dynamic dummy exists

echo "----------------------\n";

$v = $p->x;		// try to get at an invisible property; can't. The runtime sees that x
                // exists, but is invisible, so it calls __get to search for a dynamic
                // property of that name, which fails. NULL is returned.
var_dump($v);

echo "----------------------\n";

var_dump(isset($p->x));     // test if x exists and is accessible, or is dynamic
var_dump($p->__isset('x')); // test if x exists and is accessible, or is dynamic

$p->x = 200;
var_dump($p->x);

var_dump(isset($p->x));     // test if x exists and is accessible, or is dynamic
var_dump($p->__isset('x')); // test if x exists and is accessible, or is dynamic

echo "----------------------\n";

$p->color = "red";          // set dynamic property
$v = $p->color;             // get dynamic property
echo "color: $v\n";

echo "----------------------\n";

var_dump(isset($p->color));	// test if color exists and is accessible, or is dynamic

echo "----------------------\n";

$v = $p->dummy = 555;
echo "\$v: $v, dummy: " . $p->dummy . "\n";

$v = $p->color = "White";       // this calls __set but not __get
echo "\$v: $v, color: " . $p->color . "\n";

echo "----------------------\n";

var_dump(isset($p->dummy));
var_dump($p->__isset('dummy')); // test if x exists and is accessible, or is dynamic
$p->__unset('dummy');
var_dump(isset($p->dummy));
var_dump($p->__isset('dummy')); // test if x exists and is accessible, or is dynamic

unset($p->abc);             // request to unset a non-existent is ignored
unset($p->x);               // request to unset an inaccessible is ignored
var_dump(isset($p->dummy));
unset($p->dummy);           // request to unset a declared accessible is OK
var_dump(isset($p->dummy));

var_dump(isset($p->color));
unset($p->color);           //
var_dump(isset($p->color));

echo "----------------------\n";

class X
{
    public function __destruct()
    {
        echo __METHOD__ . "\n";
    }
}

///*
$p->thing = new X;  // set dynamic property to an instance having a destructor
$v = $p->thing;
var_dump($v);

//unset($p->thing);   // was sort-of expecting this to trigger the destructor, but ...
//$p->__unset('thing');
//echo "unset(\$p->thing) called\n";
//*/

echo "----------------------\n";

// show that attempts to use a non-existent property cause one to be created
// even in the absence of the __set/__get machinery.

class Test {}

$x1 = new Test;
$x1->p1 = 23;
$x1->p2 = "Hello";
var_dump($x1);

echo "----------------------\n";

foreach ($x1 as $key => $value)
{
    echo "key $key has a value of $value\n";
}

echo "----------------------\n";

$x2 = new Test;
$x2->p3 = FALSE;
var_dump($x2);

echo "----------------------\n";

foreach ($x2 as $key => $value)
{
    echo "key $key has a value of $value\n";
}

echo "----------------------\n";

$x3 = new Test;
for ($i = 4; $i <= 10; ++$i)
{
    $q = "p$i";
    $x3->$q = 999;
}
var_dump($x3);

echo "----------------------\n";

foreach ($x3 as $key => $value)
{
    echo "key $key has a value of $value\n";
}

echo "----------------------\n";

// However, this doesn't work for non-existent methods

// $x1->m1();      // Call to undefined method Test::m1()


// at program termination, the destructor for the dynamic property is called
--EXPECT--
----------------------
int(-100)
Point::__get(DUMmy)
NULL
dummy: 
Point::__get(dummy)
dynamic dummy: 
Point::__set(dummy, xx)
dummy: 987
Point::__get(dummy)
dynamic dummy: 456
----------------------
bool(true)
Point::__isset(dummy)
bool(true)
----------------------
Point::__get(x)
NULL
----------------------
Point::__isset(x)
bool(false)
Point::__isset(x)
bool(false)
Point::__set(x, xx)
Point::__get(x)
int(200)
Point::__isset(x)
bool(true)
Point::__isset(x)
bool(true)
----------------------
Point::__set(color, xx)
Point::__get(color)
color: red
----------------------
Point::__isset(color)
bool(true)
----------------------
$v: 555, dummy: 555
Point::__set(color, xx)
Point::__get(color)
$v: White, color: White
----------------------
bool(true)
Point::__isset(dummy)
bool(true)
Point::__unset(dummy)
bool(true)
Point::__isset(dummy)
bool(false)
Point::__unset(abc)
Point::__unset(x)
bool(true)
Point::__isset(dummy)
bool(false)
Point::__isset(color)
bool(true)
Point::__unset(color)
Point::__isset(color)
bool(false)
----------------------
Point::__set(thing, xx)
Point::__get(thing)
object(X)#2 (0) {
}
----------------------
object(Test)#3 (2) {
  ["p1"]=>
  int(23)
  ["p2"]=>
  string(5) "Hello"
}
----------------------
key p1 has a value of 23
key p2 has a value of Hello
----------------------
object(Test)#4 (1) {
  ["p3"]=>
  bool(false)
}
----------------------
key p3 has a value of 
----------------------
object(Test)#5 (7) {
  ["p4"]=>
  int(999)
  ["p5"]=>
  int(999)
  ["p6"]=>
  int(999)
  ["p7"]=>
  int(999)
  ["p8"]=>
  int(999)
  ["p9"]=>
  int(999)
  ["p10"]=>
  int(999)
}
----------------------
key p4 has a value of 999
key p5 has a value of 999
key p6 has a value of 999
key p7 has a value of 999
key p8 has a value of 999
key p9 has a value of 999
key p10 has a value of 999
----------------------
X::__destruct