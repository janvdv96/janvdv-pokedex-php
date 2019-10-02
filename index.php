<?php

declare(strict_types=1);

// Function for fetching and returning a JSON file
function fetcher(string $url): array
{
    $data = file_get_contents($url);
    return json_decode($data, true);
}

// Function to get the entire evolution tree and putting them in an array
function evoCatcher(array $evolutionData): array
{
    $evoList = [];
    $evoList[] = $evolutionData["chain"]["species"]["name"];
    foreach ($evolutionData["chain"]["evolves_to"] AS $evolvesTo) {
        $evoList[] = $evolvesTo["species"]["name"];
        foreach ($evolvesTo["evolves_to"] AS $evolvesToTo) {
            $evoList[] = $evolvesToTo["species"]["name"];
            foreach ($evolvesToTo["evolves_to"] AS $evolvesToToTo) {
                $evoList[] = $evolvesToToTo["species"]["name"];
            }
        }
    }
    return $evoList;
}

// Function to gather all english flavor texts into one array
function flavorCatcher(array $speciesData) : array {
    $flavor_en = [];
    foreach ($speciesData["flavor_text_entries"] AS $flavor_text) {
        if ($flavor_text["language"]["name"] === "en") {
            $flavor_en[] = $flavor_text["flavor_text"];
        }
    }
    return $flavor_en;
}

// Check if the form has been filled: if true -> run that search term, if false -> run first pokemon
if (isset($_GET["pokemon"])) {
    if ($_GET["pokemon"] != null) {
        $data = fetcher("https://pokeapi.co/api/v2/pokemon/" . strtolower($_GET["pokemon"]));
    } else {
        $data = fetcher("https://pokeapi.co/api/v2/pokemon/1");
    }
} else {
    $data = fetcher("https://pokeapi.co/api/v2/pokemon/1");
}

// Fetch two more JSON files with fetcher function, needed for flavor_text and evolutions
$speciesData = fetcher($data["species"]["url"]);
$evolutionData = fetcher($speciesData["evolution_chain"]["url"]);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
          crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="<?php echo $data["sprites"]["front_default"]; ?>" type="image/x-icon"/>
</head>
<body>
<div class="jumbotron vertical-center">
    <div class="container container-fluid p-4 mt-2">
        <form action="index.php" method="get" class="mb-3">
            <input type="text" name="pokemon" value="<?php // Display the name of the pokemon in the input field
            echo ucfirst($data["name"]);
            ?>" class="rounded-pill text-center thick-border">
            <input type="submit" value="Search" class="rounded-pill thick-border">
        </form>
        <div class="row">
            <div class="col-5">
                <div id="nameBox" class="mx-auto">
                    <span id="nameDisplay">
                        <?php // Display the name and ID of the pokemon
                        echo $data["name"] . ", " . $data["id"];
                        ?>
                    </span>
                </div>
                <div id="infoDisplay" class="mx-auto">
                    <table class="mx-auto mt-2">
                        <tbody>
                        <tr>
                            <td>Height:</td>
                            <td id="heightDisplay">
                                <?php echo ($data["height"] * 10) . " cm"; ?> <!-- Display height -->
                            </td>
                        </tr>
                        <tr>
                            <td>Weight:</td>
                            <td id="weightDisplay">
                                <?php echo floor($data["weight"] / 10) . " kg"; ?> <!-- Display weight -->
                            </td>
                        </tr>
                        <tr>
                            <td>Base Exp:</td>
                            <td id="expDisplay">
                                <?php echo $data["base_experience"]; ?> <!-- Display the base exp -->
                            </td>
                        </tr>
                        <tr id="abiDisplay">
                            <td>Abilities:</td>
                            <td>
                                <ul id="abiList">
                                    <?php // Display all the abilities of the pokemon inside a <ul>
                                    foreach ($data["abilities"] AS $ability) {
                                        echo '<li>' . $ability["ability"]["name"] . '</li>';
                                    } ?>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <?php // get all the types from the pokemon, and display the icons of said type
                            foreach ($data["types"] AS $key => $type) {
                                echo '<td id="typesDisplay' . ($key + 1) . '">';
                                echo '<img src="src/icons/' . $type["type"]["name"] . '.png"/>';
                                echo '</td>';
                            } ?>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-3 p-3">
                <img id="imgDisplay" alt="pokemon" onclick="imgChange()" class="" src="
                <?php // Display image of the pokemon, if null -> display a pokeBall image
                if ($data["sprites"]["front_default"] != null) {
                    echo $data["sprites"]["front_default"];
                } else {
                    echo 'src/pokeball.png';
                } ?>">
                <div class="row mt-2 justify-content-center">
                    <button class="rounded-pill thick-border mr-2" onclick="imgChange()">Shiny</button>
                    <button class="rounded-pill thick-border" onclick="rotationChange()">Flip</button>
                </div>
            </div>
            <div class="col">
                <div id="flavorDisplay">
                    <?php // Gather all english flavorTexts and display a random one from that array
                    $flavor_en = flavorCatcher($speciesData);
                    $flavor_rand = array_rand($flavor_en);
                    echo '<i>' . $flavor_en[$flavor_rand] . '</i>'; ?>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-5">
                <div id="moveBox" class="mx-auto">
                    <ul id="moveList"><strong id="moveSpan">Moves</strong>
                        <?php // Get all possible moves and display 4 at random, if the amount of moves < 4, display them in order
                        $moveList = [];
                        foreach ($data["moves"] AS $move) {
                            $moveList[] = $move["move"]["name"];
                        }
                        if (count($moveList) >= 4) {
                            for ($i = 0; $i < 4; $i++) {
                                $random_move = array_rand($moveList);
                                echo '<li>' . ucfirst($moveList[$random_move]) . '</li>';
                                unset($moveList[$random_move]);
                            }
                        } else {
                            for ($i = 0; $i < count($moveList); $i++) {
                                echo '<li>' . ucfirst($moveList[$i]) . '</li>';
                            }
                        } ?>
                    </ul>
                </div>
            </div>
            <div class="col-6">
                <div class="row">
                    <div id="evoDisplay">
                        <table class="mx-auto justify-content-center">
                            <tr id="evoTarget">
                                <?php //Get all evolution forms from a pokemon species and display img of them in a table
                                $evoList = evoCatcher($evolutionData);
                                foreach ($evoList AS $evo) {
                                    $evoData = fetcher("https://pokeapi.co/api/v2/pokemon/" . $evo);
                                    echo '<td  class="grow">';
                                    echo '<a href="index.php?pokemon=' . $evo . '">'; // added an href so the user can search for that evolution
                                    echo '<img src="' . $evoData["sprites"]["front_default"] . '"/>';
                                    echo '</a>';
                                    echo '</td>';
                                } ?>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-2 mx-auto justify-content-center">
                    <a href="index.php?pokemon=<?php echo($data["id"] - 1) ?>">
                        <button class="rounded-pill mr-2 thick-border" <?php
                        if ($data["id"] === 1) {
                            echo 'disabled=disabled"';
                        } ?>>Prev
                        </button>
                    </a>
                    <a href="index.php?pokemon=<?php echo($data["id"] + 1) ?>">
                        <button class="rounded-pill thick-border" <?php
                        if ($data["id"] === 807) {
                            echo 'disabled="disabled"';
                        } ?>>Next
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function imgChange() {
        if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["front_default"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["front_shiny"]?>"
        } else if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["front_shiny"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["front_default"]?>"
        } else if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["back_default"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["back_shiny"]?>"
        } else if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["back_shiny"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["back_default"]?>"
        }
    }

    function rotationChange() {
        if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["front_default"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["back_default"]?>"
        } else if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["back_default"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["front_default"]?>"
        } else if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["front_shiny"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["back_shiny"]?>"
        } else if (document.getElementById("imgDisplay").src === "<?php echo $data["sprites"]["back_shiny"]?>") {
            document.getElementById("imgDisplay").src = "<?php echo $data["sprites"]["front_shiny"]?>"
        }
    }
</script>
</body>
</html>