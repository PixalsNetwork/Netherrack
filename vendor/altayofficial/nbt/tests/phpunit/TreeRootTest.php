<?php

namespace pocketmine\nbt;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\tag\IntTag;
use function str_repeat;

class TreeRootTest extends TestCase{

	public function testNameLength() : void{
		new TreeRoot(new IntTag(1), str_repeat(".", 0x7fff)); //ok

		$this->expectException(\InvalidArgumentException::class);
		new TreeRoot(new IntTag(1), str_repeat(".", 0x7fff + 1)); //error
	}
}
