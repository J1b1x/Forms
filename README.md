# Forms

![php](https://img.shields.io/badge/php-8.0-informational)
![api](https://img.shields.io/badge/pocketmine-4.0-informational)

This is a PocketMine-MP 4.0 form library with PHP 8.0 support and high quality code

## Credits
This API is a spoon of [Frago's](https://github.com/Frago9876543210) [forms library](https://github.com/Frago9876543210/forms) and also includes a ServerSettingsForm which is inspired of [skymin's](https://github.com/sky-min) [ServerSettingsForm](https://github.com/sky-min/ServerSettingForm) virion. 

## Code samples

+ [ServerSettingsForm](#serversettingsform)
  - [Registering the packet handler](#in-order-to-send-a-serversettingsform-you-first-need-to-register-the-packet-handler-by-doing)
  - [Sending a ServerSettingsForm](#in-your-plugin-base-once-you-have-registered-the-packet-handler-you-can-just-use-the-serversettingsformevent)
+ [ModalForm](#modalform)
    - [Using ModalForm to represent "yes" / "no" button clicks as `bool` in closure](#using-modalform-to-represent-yes--no-button-clicks-as-bool-in-closure)
    - [Short version of ModalForm to confirm any action](#short-version-of-modalform-to-confirm-any-action)
+ [MenuForm](#menuform)
    - [Using MenuForm to display buttons with icons from URL and path](#using-menuform-to-display-buttons-with-icons-from-url-and-path)
    - [Creating MenuForm from array of strings (i.e. from `string[]`) with modern syntax of matching button clicks](#creating-menuform-from-array-of-strings-ie-from-string-with-modern-syntax-of-matching-button-clicks)
    - [Appending MenuForm with new options to handle different permissions](#appending-menuform-with-new-options-to-handle-different-permissions)
+ [CustomForm](#customform)
    - [Using CustomForm with strict-typed API](#using-customform-with-strict-typed-api)
    - [Using CustomForm with less strict-typed API](#using-customform-with-less-strict-typed-api)

### ServerSettingsForm

#### The ServerSettingsForm is similar to a [CustomForm](#customform) and has the same elements, but will be displayed in the player's game-settings ui
#### In order to send a ServerSettingsForm you first need to **register** the packet handler by doing

```php
protected function onEnable(): void{
    \Jibix\Forms\Forms::register($this);
}
```
#### in your plugin base. Once you have registered the packet handler, you can just use the ServerSettingsFormEvent

```php
public function onServerSettingsForm(ServerSettingsFormEvent $event): void{
    $player = $event->getPlayer();
    if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return; //Not an operator
    (new ServerSettingsForm(
        "Title!",
        [
            new Label("Want to adjust some server settings? Just do it!"),
            new Input("Motd", "§cBest Server!", Server::getInstance()->getNetwork()->getName(), function (Player $player, Input $input): void{
                Server::getInstance()->getNetwork()->setName($input->getName());
            }),
        ],
        function (Player $player, CustomFormResponse $response): void{
           $player->sendMessage("Done! You successfully adjusted the server settings.") 
        },
        Image::path("textures/ui/diamond") //Set the icon of the form
    ))
}
```

### ModalForm

#### Using ModalForm to represent "yes" / "no" button clicks as `bool` in closure

```php
$player->sendForm(new ModalForm("A small question", "Is our server cool?",
	//result of pressing the "yes" / "no" button is written to a variable $choice
	function (Player $player, bool $choice): void{
		$player->sendMessage($choice ? "Thank you" : "We will try to become better");
	}
));
```

#### Short version of ModalForm to confirm any action

```php
$player->sendForm(ModalForm::confirm("Teleport request", "Do you want to accept it?",
	//called only when the player selects the "yes" button
	function (Player $player): void{
		$player->sendMessage("*teleporting*");
	}
));
```

### MenuForm

#### Using MenuForm to display buttons with icons from URL and path

```php
$player->sendForm(new MenuForm("Select server", "Choose server", [
	//buttons without icon
	new Button("SkyWars #1"),
	new Button("SkyWars #2"),
	//URL and path are supported for image
	new Button("BedWars #1", null, Image::url("https://static.wikia.nocookie.net/minecraft_gamepedia/images/f/f0/Melon_JE2_BE2.png")),
	new Button("BedWars #2", null, Image::path("textures/items/apple.png")),
	//If you have some dynamic images you can use Image::detect
	new Button("Dyamic #1", null, Image::detect($image))
], function (Player $player, Button $selected): void{
	$player->sendMessage("You selected: " . $selected->getText());
	$player->sendMessage("Index of button: " . $selected->getValue());
}, function (Player $player): void{
    $player->sendMessage("You closed the server selector!");
}));
```

#### Shorther/simpler MenuForm, you can directly set the button's onSubmit callback, which can be useful if you have object foreaches 

```php
foreach ($objectArray as $key => $object) {
    $buttons[] = new Button("Key #$key", function (Player $player, Button $selected) use ($key, $object): void{
        $player->sendMessage("You have selected the key #$key");
        //Do something with $object
    })
}
$buttons[] = Button::back("Do you want to go back?", function (Player $player): void{
    $player->sendMessage("You clicked on the back button!");
    //Go back
}); //returns a "Back button" with a nice image
$buttons[] = Button::close(); //returns a "Close Button" with a nice image
$player->sendForm(new MenuForm("Select key", "Choose key", $buttons));
```

### CustomForm

#### Using CustomForm with strict-typed API

```php
$player->sendForm(new CustomForm("Enter data", [
	new Dropdown("Select product", ["beer", "cheese", "cola"]),
	new Input("Enter your name", "Bob"),
	new Label("I am label!"), //Note: get<BaseElement>() does not work with label
	new Slider("Select count", 0.0, 100.0, 1.0, 50.0),
	new StepSlider("Select product", ["beer", "cheese", "cola"]),
	new Toggle("Creative", $player->isCreative()),
], function (Player $player, CustomFormResponse $response): void{
	$dropdown = $response->getDropdown();
	$player->sendMessage("You selected: " . $dropdown->getSelectedOption());

	$input = $response->getInput();
	$player->sendMessage("Your name is " . $input->getValue());

	$slider = $response->getSlider();
	$player->sendMessage("Count: " . $slider->getValue());

	$stepSlider = $response->getStepSlider();
	$player->sendMessage("You selected: " . $stepSlider->getSelectedOption());

	$toggle = $response->getToggle();
	$player->setGamemode($toggle->getValue() ? GameMode::CREATIVE() : GameMode::SURVIVAL());
}));
```

#### Using CustomForm with less strict-typed API

```php
$player->sendForm(new CustomForm("Enter data", [
	new Dropdown("Select product", ["beer", "cheese", "cola"]),
	new Input("Enter your name", "Bob"),
	new Label("I am label!"), //Note: get<BaseElement>() does not work with label
	new Slider("Select count", 0.0, 100.0, 1.0, 50.0),
	new StepSlider("Select product", ["beer", "cheese", "cola"]),
	new Toggle("Creative", $player->isCreative()),
], function (Player $player, CustomFormResponse $response): void{
	/** @var bool $enableCreative */ //type-hint for phpstan
	[$product1, $username, $count, $product2, $enableCreative] = $response->getValues();

	$player->sendMessage("You selected: $product1");
	$player->sendMessage("Your name is $username");
	$player->sendMessage("Count: $count");
	$player->sendMessage("You selected: $product2");
	$player->setGamemode($enableCreative ? GameMode::CREATIVE() : GameMode::SURVIVAL());
}));
```

#### Using CustomForms elements with directly setting their onSubmit callback

```php
$player->sendForm(new CustomForm("Enter data", [
	new Dropdown("Select product #1", ["beer", "cheese", "cola"], function (Player $player, Dropdown $dropdown): void{
	    $player->sendMessage("Your first product is {$dropdown->getSelectedOption()}");
	}),
	new Input("Enter your name", "Bob", function (Player $player, Input $input): void{
	    $player->sendMessage("You entered the name: {$input->getValue()}!");
	}),
	new Label("I am label!"),
	new Slider("Select count", 0.0, 100.0, 1.0, 50.0, function (Player $player, Slider $slider): void{
	    $player->sendMessgae("You selected a count of {$slider->getValue()}");
	}),
	new StepSlider("Select product #2", ["beef", "fanta", "chips"], function (Player $player, StepSlider $slider): void{
	    $player->sendMessage("Your second product is {$slider->getSelectedOption()}");
	}),
	new Toggle("Creative", $player->isCreative(), function (Player $player, Toggle $toggle): void{
	    $player->setGamemode($toggle->getValue() ? GameMode::CREATIVE() : GameMode::SURVIVAL());
	}),
]));
```