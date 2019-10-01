<?php

function fetcher($url)
{
    $data = file_get_contents($url);
    return json_decode($data, true);
}

if (isset($_GET["pokemon"])){
    $data = fetcher("https://pokeapi.co/api/v2/pokemon/" . $_GET["pokemon"]);
} else{
    $data = fetcher("https://pokeapi.co/api/v2/pokemon/1");
}

$speciesData = fetcher($data["species"]["url"]);
$evolutionData = fetcher($speciesData["evolution_chain"]["url"]);

?>

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
</head>
<body>
<div class="jumbotron vertical-center">
    <div class="container container-fluid p-4 mt-2">
        <form action="index.php" method="get">
            <input type="text" name="pokemon" value="" class="rounded">
            <input type="submit" value="Search" class="rounded-pill">
        </form>
        <div class="row">
            <div class="col-5">
                <div id="nameBox" class="mx-auto">
                    <span id="nameDisplay">
                        <?php
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
                                <?php
                                echo ($data["height"] * 10) . " cm";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Weight:</td>
                            <td id="weightDisplay">
                                <?php
                                echo floor($data["weight"] * 0.45) . " kg";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Base Exp:</td>
                            <td id="expDisplay">
                                <?php
                                echo $data["base_experience"];
                                ?>
                            </td>
                        </tr>
                        <tr id="abiDisplay">
                            <td>Abilities:</td>
                            <td>
                                <ul id="abiList">
                                    <?php
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
                            <?php
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
                <img id="imgDisplay" class="" src="<?php echo $data["sprites"]["front_default"]; ?>" alt="pokemon">
            </div>
            <div class="col">
                <div id="flavorDisplay"></div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-5">
                <div id="moveBox" class="mx-auto">
                    <ul id="moveList"><strong id="moveSpan">Moves</strong>
                        <?php
                        $moveList = [];
                        foreach ($data["moves"] AS $move) {
                            $moveList[] = $move["move"]["name"];
                        }
                        if (count($moveList) >= 4){
                            for ($i = 0; $i < 4; $i++) {
                                $random_move = array_rand($moveList);
                                echo '<li>';
                                echo $moveList[$random_move];
                                echo '</li>';
                            }
                        } else{
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
                                <?php
                                $evoList = [];
                                $evoList[] = $evolutionData["chain"]["species"]["name"];
                                foreach ($evolutionData["chain"]["evolves_to"] as $evolvesTo){
                                    $evoList[] = $evolvesTo["species"]["name"];
                                    foreach ($evolvesTo["evolves_to"] AS $evolvesToTo){
                                        $evoList[] = $evolvesToTo["species"]["name"];
                                        foreach ($evolvesToTo["evolves_to"] AS $evolvesToToTo){
                                            $evoList[] = $evolvesToToTo["species"]["name"];
                                        }
                                    }
                                }
                                foreach ($evoList AS $evo){
                                    $evoData = fetcher("https://pokeapi.co/api/v2/pokemon/" . $evo);
                                    echo '<td>';
                                    echo '<img src="' . $evoData["sprites"]["front_default"] . '"/>';
                                    echo '</td>';

                                }
                                ?>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

<?php
var_dump($evoList);
echo '<hr>';
var_dump($data);
?>