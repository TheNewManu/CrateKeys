<?php

namespace TheNewManu\CrateKey;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat as TF;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\item\ItemFactory;
use pocketmine\block\EnderChest;
use pocketmine\level\sound\FizzSound;
use pocketmine\level\particle\DustParticle;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use TheNewManu\CrateKey\Tasks\CloseChest;
use TheNewManu\CrateKey\Tasks\DespawnItem;

class Main extends PluginBase implements Listener {

    /** @var Config $language */
    private $language;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->registerLanguage();
        $this->registerCommands();
        foreach($this->getAllKeys() as $key => $array) {
            if(!isset($array["ID"]) or !isset($array["Damage"]) or !isset($array["Description"])) {
                $this->getLogger()->alert("Invalid Key set in keys in config.yml, disabling plugin");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }
            if(!isset($this->getConfig()->getAll()["rewards"][$key])) {
                $this->getLogger()->alert("Key not set in rewards in config.yml, disabling plugin");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }
        }
    }
    
    public function registerLanguage(): void {
        $lang = $this->getConfig()->get("language", "eng");
        $pathToLangs = $this->getFile() . "resources" . DIRECTORY_SEPARATOR . "lang" . DIRECTORY_SEPARATOR;
        if(!file_exists($pathToLangs . "$lang.ini")) {
            $lang = "eng";
            $this->getLogger()->error("No valid language has been selected. English has been auto selected.");
        }
        $this->language = new Config($pathToLangs . "$lang.ini", Config::PROPERTIES);
    }

    public function registerCommands(): void {
        $commands = [
            "key" => "\TheNewManu\CrateKey\Commands\KeyCommand",
            "keyall" => "\TheNewManu\CrateKey\Commands\KeyAllCommand",
            "keylist" => "\TheNewManu\CrateKey\Commands\KeyListCommand"
        ];
        foreach ($commands as $cmd => $class) {
            $this->getServer()->getCommandMap()->register(strtolower($this->getName()), new $class($this));
        }
    }
    
    /**
     * @param string $str
     * @param array $params
     * @return string
     */
    public function translateString(string $str, array $params = []): string {
        /** @var string $str */
        $str = $this->getLanguage()->get($str);
        foreach($params as $i => $p){
            $str = str_replace("{%$i}", $p, $str);
        }
        return TF::colorize($str);
    }
    
    /**
     * @param EnderChest $chest
     * @param Item $item
     * @param Player $player
     */
    public function spawnItem(EnderChest $chest, $item, Player $player): void {
        $pk = new AddItemActorPacket();
        $pk->entityRuntimeId = Entity::$entityCount++;
        $pk->item = $item;
        $pk->position = $chest->add(0.5, 1.3, 0.5);
        $player->dataPacket($pk);
        $this->getScheduler()->scheduleDelayedTask(new DespawnItem($this, $player, $pk), 15 * 3);
    }

    /**
     * @param Player $player
     * @param Block $chest
     */
    public function spawnOpenChest(Player $player, Block $chest): void {
        if($chest instanceof EnderChest) {
            $pk = new BlockEventPacket;
            $pk->x = $chest->getX();
            $pk->y = $chest->getY();
            $pk->z = $chest->getZ();
            $pk->eventType = 1;
            $pk->eventData = 2;
            $player->dataPacket($pk);
            $this->getScheduler()->scheduleDelayedTask(new CloseChest($this, $player, $chest), 15 * 3);
        }
    }

    /**
     * @param Player $player
     * @param string $key
     * @param Block $block
     */
    public function giveRewards(Player $player, string $key, Block $block): void {
        $rewards = $this->getRewards($key);
        $reward = $rewards[array_rand($rewards)];
        $item = explode(":", $reward["spawn-item"]);
        $this->spawnItem($block, ItemFactory::get((int)$item[0], (int)$item[1], (int)$item[2]), $player);
        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $reward["command"]));
        $player->sendMessage(str_replace("{player}", $player->getName(), $reward["message"]));
        $player->addTitle($reward["title"], $reward["subtitle"]);
    }

    /**
     * @param PlayerInteractEvent $event
     */
     public function onInteract(PlayerInteractEvent $event): void {
         $player = $event->getPlayer();
         $block = $event->getBlock();
         $level = $player->getLevel();
         $item = $player->getInventory()->getItemInHand();
         if($block instanceof EnderChest){
             if(isset($item->getLore()[0]) and isset($this->getAllKeys()[$item->getLore()[0]])){
                 $event->setCancelled(true);
                 $item->pop();
                 $player->getInventory()->setItemInHand($item);
                 $this->giveRewards($player, $item->getLore()[0], $block);
                 $this->spawnOpenChest($player, $block);
                 $level->addSound(new FizzSound($player));
                 $x = $block->getX() + 0.5;
                 $y = $block->getY();
                 $z = $block->getZ() + 0.5;
                 $center = new Vector3($x, $y, $z);
                 $particle = new DustParticle($center, rand(1,300), rand(1,300), rand(1,300), 1);
                 for($yaw = 0, $y = $center->y; $y < $center->y + 3; $yaw += (M_PI * 2) / 80, $y += 1 / 80){
                     $x = -sin($yaw) + $center->x;
                     $z = cos($yaw) + $center->z;
                     $particle->setComponents($x, $y, $z);
                     $level->addParticle($particle);
                 }
             }
         }
    }
     
    /**
     * @return Config
     */
    public function getLanguage(): Config {
        return $this->language;
    }
     
    /**
     * @return array
     */
    public function getAllKeys(): array {
        return $this->getConfig()->get("keys");
    }
     
    /**
     * @return array
     */
    public function getRewards(string $key): array {
        return $this->getConfig()->getNested("rewards.$key");
    }
}
