<?php
require_once 'src/helpers/cli_helper.php';

enum InterfaceOptions: string {
    case ShowProducts = "Show all products";
    case ShowCoins = "Show accepted coins";
    case SelectProduct = "Select Products";
    case ExitProgram = "Exit";
}

class VendingMachineController {
    /** @var array Vending Machine products */
    private $products = [];
    /** @var array allowed to insert coins */
    private $allowedCoins = [];

    /**
     * Controller constructor
     *
     * @param array $products
     * @param array $allowedCoins
     */
    public function __construct(
        array $products,
        array $allowedCoins
    )
    {
        $this->allowedCoins = $allowedCoins;
        $this->products = $products;

        rsort($this->allowedCoins, SORT_NUMERIC );
    }

    /**
     * CLI interface output and args processing
     *
     * @param string $args
     *
     * @return void
     */
    public function runInterface() {
        $this->printProducts();

        while (true) {
            printLine("");
            $this->printOptions();

            $option = $this->getOption();
            if (!$option) {
                continue;
            }

            $result = $this->selectOption($option);
            if (!$result) {
                break;
            }
        }
    }

    /**
     * Show options according to user input
     *
     * @param InterfaceOptions $option
     * @return bool
     */
    private function selectOption(InterfaceOptions $option): bool {
        switch ($option) {
            case InterfaceOptions::SelectProduct:
                $this->byProduct();
                return true;
            case InterfaceOptions::ShowProducts:
                $this->printProducts();
                return true;
            case InterfaceOptions::ShowCoins:
                $this->printAcceptedCoins();
                return true;
            case InterfaceOptions::ExitProgram:
                return false;
        }

        return false;
    }

    /**
     * Product purchase function
     *
     * @return void
     */
    private function byProduct()
    {
        $this->printProducts();
        printLine("");
        printLine("Please enter product id:");

        $arg = readline();

        if (isset($this->products[$arg])) {
            printLine(
                "Current product: %d:\t%s - %f",
                $arg,
                $this->products[$arg]['name'],
                $this->products[$arg]['price']
            );
            printLine("");
            $this->printAcceptedCoins();
            printLine("");

            $this->processCoins($this->products[$arg]['price']);
        } else {
            printLine("incorrect product id");
        }

    }

    /**
     * Count user coins and display change
     *
     * @param float $productPrice
     *
     * @return void
     */
    private function processCoins(float $productPrice)
    {
        printLine("Insert coins one by one");
        printLine("print 0 to finish inserting");

        $userCoins = 0;

        do {
            $arg = readline();

            if (in_array($arg, $this->allowedCoins)) {
                $userCoins += $arg;
                printLine("current ammount: %f", $userCoins);

                if ($userCoins == $productPrice) {
                    printLine("Product was purchased");
                    $arg = 0;
                } else if ($userCoins > $productPrice) {
                    $this->processChange($userCoins - $productPrice);
                    $arg = 0;
                }
            } else {
                printLine('incorrect coin, skipped');
            }
        } while ($arg != 0);
    }

    /**
     * count user change
     *
     * @param float $userChange
     *
     * @return void
     */
    private function processChange(float $userChange)
    {
        printLine("Current change: %f", $userChange);
        $changeResult = [];
        foreach ($this->allowedCoins as $coin) {
            $coinsAmount = 0;
            while ($userChange > $coin) {
                $coinsAmount += 1;
                $userChange -= $coin;
            }

            if ($coinsAmount) {
                $changeResult[$coin] = $coinsAmount;
            }
        }

        printLine("Your change:");
        foreach ($changeResult as $key => $value) {
            printLine("%f - %d", $key, $value);
        }
    }

    /**
     * print machine products
     *
     * @return void
     */
    private function printProducts($isSelect = false)
    {
        printLine("Vending Machine products:");
        foreach ($this->products as $id => $product) {
            printLine("%d:\t%s - %f", $id, $product['name'], $product['price']);
        }
    }

    /**
     * Print accepted coins
     *
     * @return void
     */
    private function printAcceptedCoins()
    {
        printLine("Accepted coins:");
        foreach ($this->allowedCoins as $coin) {
            printLine("%f", $coin);
        }
    }

    /**
     * print allowed options
     *
     * @return void
     */
    private function printOptions()
    {
        foreach (InterfaceOptions::cases() as $key => $option) {
            printLine("%d: \t%s", $key, $option->value);
        }
    }

    /**
     * returns InterfaceOptions or display error
     *
     * @return null|InterfaceOptions
     */
    private function getOption(): null | InterfaceOptions {
        $option = null;
        $arg = readline();

        try {
            $option = InterfaceOptions::cases()[$arg];
        } catch (Throwable $e) {
            printLine("incorrect value\nerror: %s", $e->getMessage());
        }

        return $option;
    }
}
