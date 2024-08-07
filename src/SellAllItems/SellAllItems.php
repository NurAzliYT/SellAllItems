<?php

namespace SellAllItems;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player; // Change here
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class SellAllItems extends PluginBase {

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($sender instanceof Player) {
            $this->openSellUI($sender);
        } else {
            $sender->sendMessage("This plugin can only be used by players in-game.");
        }
        return true;
    }

    private function openSellUI(Player $player): void {
        $form = new SimpleForm(function (Player $player, ?int $selectedOption) {
            if ($selectedOption !== null) {
                $this->sellAllItems($player);
            }
        });
        $form->setTitle("Sell All Items");
        $form->setContent("Click to sell all items (except Bedrock) and earn money.");
        $form->addButton("Sell All");
        $form->sendToPlayer($player);
    }

    private function sellAllItems(Player $player): void {
        $sellPrice = 1; // Selling price (adjust as needed)
        $totalMoney = 0;

        foreach ($player->getInventory()->getContents() as $slot => $item) {
            // Bedrock ID is 7
            if ($item->getId() !== 7) {
                $player->getInventory()->clear($slot);
                $totalMoney += $sellPrice;
            }
        }

        $bedrockEconomy = $this->getServer()->getPluginManager()->getPlugin("BedrockEconomy");
        if ($bedrockEconomy !== null && method_exists($bedrockEconomy, 'addMoney')) {
            $bedrockEconomy->addMoney($player->getName(), $totalMoney);
            $player->sendMessage("You have sold all items (except Bedrock) and earned " . $totalMoney . " money.");
        } else {
            $this->getLogger()->warning("BedrockEconomy plugin not found or does not support addMoney method. Money not added.");
        }
    }
}
