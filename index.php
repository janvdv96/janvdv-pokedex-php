<?php

// Function for fetching and returning a JSON file
function fetcher($url)
{
    $data = file_get_contents($url);
    return json_decode($data, true);
}

// Check if the form has been filled: if true -> run that search term, if false -> run first pokemon
if (isset($_GET["pokemon"])) {
    if ($_GET["pokemon"] != null) {
        $data = fetcher("https://pokeapi.co/api/v2/pokemon/" . $_GET["pokemon"]);
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
    <!-- Start HTML -->
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
                echo $data["name"];
                ?>" class="rounded text-center thick-border">
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
                                    <?php // Display the height of the pokemon, after converting to cm from dm
                                    echo ($data["height"] * 10) . " cm";
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Weight:</td>
                                <td id="weightDisplay">
                                    <?php // Display the weight of the pokemon, after converting to kg from lbs
                                    echo floor($data["weight"] * 0.45) . " kg";
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Base Exp:</td>
                                <td id="expDisplay">
                                    <?php // Display the base exp of the Pokemon
                                    echo $data["base_experience"];
                                    ?>
                                </td>
                            </tr>
                            <tr id="abiDisplay">
                                <td>Abilities:</td>
                                <td>
                                    <ul id="abiList">
                                        <?php // Display all the abilities(passive) of the pokemon
                                        foreach ($data["abilities"] AS $ability) {
                                            echo '<li>';
                                            echo $ability["ability"]["name"] . "";
                                            echo '</li>';
                                        }
                                        ?>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <?php // get all the types from the pokemon, and display the icons of said type
                                foreach ($data["types"] AS $key => $type) {
                                    echo '<td id="typesDisplay' . ($key + 1) . '">';
                                    echo '<img src="src/icons/' . $type["type"]["name"] . '.png"/>';
                                    echo '</td>';
                                }
                                ?>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-3 p-3">
                    <img id="imgDisplay" onclick="imgChange()" class="" src="
                <?php // Display an image of the pokemon, if null -> display a pokeball image
                    if ($data["sprites"]["front_default"] != null) {
                        echo $data["sprites"]["front_default"];
                    } else {
                        echo 'src/pokeball.png';
                    }
                    ?>" alt="pokemon">
                    <div class="row mt-2 justify-content-center">
                        <button class="rounded-pill thick-border mr-2" onclick="imgChange()">Shiny</button>
                        <button class="rounded-pill thick-border" onclick="rotationChange()">Flip</button>
                    </div>
                </div>
                <div class="col">
                    <div id="flavorDisplay">
                        <?php // get all english flavortexts and display a random one from the array
                        $flavor_en = [];
                        foreach ($speciesData["flavor_text_entries"] AS $flavor_text) {
                            if ($flavor_text["language"]["name"] === "en") {
                                $flavor_en[] = $flavor_text["flavor_text"];
                            }
                        }
                        $flavor_rand = array_rand($flavor_en);
                        echo '<i>' . $flavor_en[$flavor_rand] . '</i>';
                        ?>
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
                                    echo '<li>';
                                    echo $moveList[$random_move];
                                    echo '</li>';
                                    unset($moveList[$random_move]);
                                }
                            } else {
                                for ($i = 0; $i < count($moveList); $i++) {
                                    echo '<li>';
                                    echo $moveList[$i];
                                    echo '</li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row">
                        <div id="evoDisplay">
                            <table class="mx-auto justify-content-center">
                                <tr id="evoTarget">
                                    <?php //Get all evolution forms from a pokemon species and display them in a table
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
                                    foreach ($evoList AS $evo) {
                                        $evoData = fetcher("https://pokeapi.co/api/v2/pokemon/" . $evo);
                                        echo '<td  class="grow">';
                                        echo '<a href="index.php?pokemon=' . $evo . '">'; // added an href so the user can search for that evolution
                                        echo '<img src="' . $evoData["sprites"]["front_default"] . '"/>';
                                        echo '</a>';
                                        echo '</td>';
                                    }
                                    ?>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-2 mx-auto justify-content-center">
                        <a href="index.php?pokemon=<?php echo ($data["id"]-1)?>"><button class="rounded-pill mr-2 thick-border">Prev</button></a>
                        <a href="index.php?pokemon=<?php echo ($data["id"]+1)?>"><button class="rounded-pill thick-border">Next</button></a>
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

<?php
var_dump($data["sprites"]);