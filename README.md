# Forms

![php](https://img.shields.io/badge/php-8.0-informational)
![api](https://img.shields.io/badge/pocketmine-4.0-informational)

This is a PocketMine-MP 4.0 form library with PHP 8.0 support and high quality code

## Credits
This API is a spoon of [Frago's](https://github.com/Frago9876543210) [forms library](https://github.com/Frago9876543210/forms) and also includes a ServerSettingsForm which is inspired of [skymin's](https://github.com/sky-min) [ServerSettingsForm](https://github.com/sky-min/ServerSettingForm) virion and a [Image loading fix](#image-fix) which is inspired by [Muqsit's](https://github.com/Muqsit) [FormImagesFix](https://github.com/Muqsit/FormImagesFix) plugin. 

## Code samples

+ [Registration](#registration)
+ [Auto-back](#auto-back)
    - [What is "auto-back"?](#auto-back-is-a-feature-that-sends-the-previous-opened-form-to-the-player-once-they-close-a-form-or-click-on-a-back-button-it-also-overwrites-back-buttons-in-a-menuformmenuform-with-close-buttons-if-theres-no-form-to-go-back-to)
    - [Toggling it](#if-you-want-to-use-the-auto-back-feature-you-just-need-to-do)
+ [Image Fix](#image-fix)
+ [ServerSettingsForm](#serversettingsform)
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
    - [From/to Data CustomForm](#fromto-data-customform-this-can-be-useful-if-you-want-to-send-the-same-ui-again-but-with-some-changes-such-as-an-error-message-label)
+ [Uncloseable Form](#uncloseable-form)
    - [Sending an uncloseable form](#an-uncloseable-form-can-be-useful-if-you-permanently-want-to-display-something-to-the-player)

### Registration

#### In order to use the [ServerSettingsForms](#serversettingsform), [ImageFix](#image-fix) and the [auto-back](#autoback) feature you first need to **register** the packet handler by doing
```php
protected function onEnable(): void{
    \Jibix\Forms\Forms::register($this);
}
```
#### Note: You only need to do this if you use this plugin as a virion, otherwise it's handled by the [Main class](https://github.com/J1b1x/Forms/blob/master/src/Jibix/Forms/Main.php)

### Auto-back
#### Auto-back is a feature that sends the previous opened form to the player once they close a form or click on a back button. It also overwrites back buttons in a [MenuForm](#menuform) with close buttons if there's no form to go back to
#### If you want to use the auto-back feature, you just need to do
```php
\Jibix\Forms\Forms::setAutoBack(true);
```

## Image fix
#### Image fix is a workaround for a MCPE MenuForm bug, where url button images take ages to load
#### To make this work you only need to do the [registration](#registration)

### ServerSettingsForm

#### The ServerSettingsForm is similar to a [CustomForm](#customform) and has the same elements, but will be displayed in the player's game-settings ui
#### Once you have registered the packet handler, you can just use the ServerSettingsFormEvent

```php
public function onServerSettingsForm(ServerSettingsFormEvent $event): void{
    $player = $event->getPlayer();
    if (!$player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return; //Not an operator
    (new ServerSettingsForm(
        "§bServer settings",
        [
            new Label("Want to adjust some server settings? Just do it!"),
            new Input("Motd", "§cBest Server!", Server::getInstance()->getNetwork()->getName(), function (Player $player, Input $input): void{
                Server::getInstance()->getNetwork()->setName($input->getName());
            }),
        ],
        function (Player $player, CustomFormResponse $response): void{
           $player->sendMessage("Done! You successfully adjusted the server settings.");
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
	//URL and path are supported for image
	new Button("SkyWars #2", null, Image::url("https://static.wikia.nocookie.net/minecraft_gamepedia/images/f/f0/Melon_JE2_BE2.png")),
	new Button("SkyWars #3", null, Image::path("textures/items/apple.png")),
	//If you have some dynamic images you can use Image::detect
	new Button("SkyWars #4", null, Image::detect($image)),
	new BackButton(), //Dynamic back button
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
$buttons[] = new BackButton(); //Dynamic back button
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

####From/to Data CustomForm. This can be useful if you want to send the same ui again, but with some changes, such as an error message label
```php
public function sendCustomForm(Player $player, array $data = [], ?string $message = null){
    $player->sendForm(new CustomForm(
        "Enter rank data",
        CustomForm::fromData([
            new Label($message ?? "Enter the ranks data!")
	        new Input("Name", "Owner"),
	        new Slider("Price", 0, 100, 1)
        ], $data),
        function (Player $player, CustomFormResponse $response): void{
            $data = $response->__toData();
            [$name, $price] = $response->getElements();
            $error = match (true) {
                strlen($name) < 4 => "§cThe name must be at least 4 chars!",
                strlen($name) > 20 => "§cThe name must be shorter than 20 chars!",
                //other checks
                default => null
            };
            if ($error !== null) {
                $this->sendCustomForm($player, $data, $error);
                return;
            }
            //Do something with the rank data
        }
    ));
}
```

### Uncloseable form

### An uncloseable form can be useful if you permanently want to display something to the player
```php
$player->sendForm(new MenuForm(
    "§cMaintenance",
    "Hello {$player->getName()}! Unfortunately we are currently in maintenance to update some server features!\nPlease come back once we announce that we're done with the update.",
    [],
    null,
    Form::uncloseable()
));
```
