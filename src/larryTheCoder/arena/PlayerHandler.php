<?php
/**
 * Adapted from the Wizardry License
 *
 * Copyright (c) 2015-2019 larryTheCoder and contributors
 *
 * Permission is hereby granted to any persons and/or organizations
 * using this software to copy, modify, merge, publish, and distribute it.
 * Said persons and/or organizations are not allowed to use the software or
 * any derivatives of the work for commercial use or any other means to generate
 * income, nor are they allowed to claim this software as their own.
 *
 * The persons and/or organizations are also disallowed from sub-licensing
 * and/or trademarking this software without explicit permission from larryTheCoder.
 *
 * Any persons and/or organizations using this software must disclose their
 * source code and have it publicly available, include this license,
 * provide sufficient credit to the original authors of the project (IE: larryTheCoder),
 * as well as provide a link to the original project.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,FITNESS FOR A PARTICULAR
 * PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace larryTheCoder\arena;


use larryTheCoder\utils\Utils;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

/**
 * This class handles everything related to the player
 * and the player side-things.
 *
 * @package larryTheCoder\arena
 */
abstract class PlayerHandler {

	// PHASE 1: SEPARATE  [Done]
	// PHASE 2: REWRITE
	// PHASE 3: TEST

	const TEAM_BLUE = 0;
	const TEAM_YELLOW = 1;
	const TEAM_GREEN = 2;
	const TEAM_RED = 3;
	const TEAM_ORANGE = 4;
	const TEAM_PURPLE = 5;

	/** @var Player[] */
	public $players = [];
	/** @var Player[] */
	public $spec = [];

	/** @var array */
	public $kills = [];
	/** @var string[] */
	public $winners = [];

	/** @var Position */
	public $cageToRemove = [];
	/** @var integer[] */
	public $claimedPedestals = [];
	/** @var Position[] */
	public $spawnPedestals = [];

	/** @var int */
	public $fallTime = 0;

	/** @var int */
	public $teammates = [];

	/** @var Arena */
	private $arena;

	public function __construct(Arena $arena){
		$this->arena = $arena;
	}

	/**
	 * @param $p
	 * @return mixed
	 */
	public abstract function kickPlayer($p);

	/**
	 * @param Player $p
	 * @param bool $kicked
	 * @return mixed
	 */
	public abstract function leaveArena(Player $p, $kicked = false);

	/**
	 * @return mixed
	 */
	public abstract function checkAlive();

	/**
	 * @return mixed
	 */
	public abstract function broadcastResult();

	/**
	 * @return mixed
	 */
	public abstract function unsetAllPlayers();

	/**
	 * @return array
	 */
	public function getAllPlayers(): array{
		return array_merge($this->players, $this->spec);
	}

	/**
	 * @param Player $p
	 * @return bool|int
	 */
	public function getPlayerMode(Player $p){
		if(isset($this->players[strtolower($p->getName())])){
			return 0;
		}
		if(isset($this->spec[strtolower($p->getName())])){
			return 1;
		}

		return false;
	}

	/**
	 * Remove cage of the player
	 *
	 * @param Player $p
	 * @return bool
	 */
	public function removeCage(Player $p): bool{
		if(!isset($this->cageToRemove[strtolower($p->getName())])){
			return false;
		}
		foreach($this->cageToRemove[strtolower($p->getName())] as $pos){
			$this->arena->getArenaLevel()->setBlock($pos, Block::get(0));
		}
		unset($this->cageToRemove[strtolower($p->getName())]);

		return true;
	}

	/**
	 * Updates the status of the arena.
	 * This changes the amount of kills represented by
	 * the players every 1 seconds.
	 */
	public function statusUpdate(){
		if($this->arena->getMode() !== Arena::ARENA_WAITING_PLAYERS){
			$i = 0;
			arsort($this->kills);
			foreach($this->kills as $player => $kills){
				$p = Server::getInstance()->getPlayer($player);
				if(!is_null($p)){
					$this->winners[$i] = ["{$p->getName()}", $kills];
				}else{
					unset($this->kills[$player]);
				}
				$i++;
			}
		}else{
			$this->winners = [];
			# Sometimes player are null
			if(!isset($this->winners[1])){
				$this->winners[0] = ["§7...", 0];
			}
			if(!isset($this->winners[2])){
				$this->winners[1] = ["§7...", 0];
			}
			if(!isset($this->winners[3])){
				$this->winners[2] = ["§7...", 0];
			}
		}
	}

	/**
	 * Get the numbers of player in arena
	 *
	 * @return int
	 */
	public function getPlayers(): int{
		return count($this->players);
	}

	/**
	 * Check if the entity is in this arena
	 *
	 * @param Entity|string $p
	 * @param bool $test
	 * @return bool
	 */
	public function inArena($p, bool $test = false): bool{
		$players = array_merge($this->players, $this->spec);
		if($p instanceof Player){
			if($test){
				Utils::sendDebug("The player node");
			}

			return isset($players[strtolower($p->getName())]);
		}

		if($test){
			Utils::sendDebug("The string node");
		}

		return isset($players[strtolower($p)]);
	}
}