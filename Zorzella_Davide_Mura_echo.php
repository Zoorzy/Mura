<?php

//table structure
//possibilmete usiamo numeri pari, grazzzzzie
define("RIGHE", 10);
define("COLONNE", 10);

define("OSTACOLO", "*");
define("MURO", "#");

//players
define("PERSONAGGIO", "C");
define("TROFEO", "P");
//define("GHOST", "X");

$mappa = array(); //Richiamata come $GLOBALS['$mappa'][][];

draw();

function draw()
{
    //echo "<h3>Welcome To</h3>";
    echo "<h1>LABIRINTO</h1>";

    riempiMatrice();
    echo '<table>';
    for ($riga = 0; $riga < RIGHE; $riga++) {
        echo '<tr>';
        for ($colonna = 0; $colonna < COLONNE; $colonna++) {
            echo '<td id="' . $riga . '_' . $colonna . '">' . $GLOBALS['$mappa'][$riga][$colonna] . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
}


function bordiMappa()
{
    //i bordi (estremi dell'array) sono MURO
    for ($riga = 0; $riga < RIGHE; $riga++) {
        for ($colonna = 0; $colonna < COLONNE; $colonna++) {
            $isWall = ($riga == 0 || $colonna == 0 || $colonna == (COLONNE - 1) || $riga == (RIGHE - 1));
            $GLOBALS['$mappa'][$riga][$colonna] =  $isWall ? MURO : "&nbsp";
        }
    }
}

function pulisciMatriceDaMuri()
{
    for ($i = 1; $i <= RIGHE - 2; $i++) {
        for ($j = 1; $j <= COLONNE - 2; $j++) {
            if ($GLOBALS['$mappa'][$i][$j] == OSTACOLO) $GLOBALS['$mappa'][$i][$j] = "&nbsp";
        }
    }
}

function generaOstacoli()
{

    pulisciMatriceDaMuri();

    for ($riga = 1; $riga < (RIGHE - 2); $riga += 2) {
        for ($colonna = 1; $colonna < (COLONNE - 1); $colonna += 2) {
            do {
                $salto_righe = rand($riga, $riga + 1);
                $salto_colonne = rand($colonna, $colonna + 1);
            } while (($salto_righe == 1 && $salto_colonne == 1) || ($salto_righe == RIGHE - 2 && $salto_colonne == COLONNE - 2));
            $GLOBALS['$mappa'][$salto_righe][$salto_colonne] = OSTACOLO;
        }
    }

    $e = esistonoPercorsiChiusi();
    echo "Esistono percorsi chiusi: " . $e . "<br>";
    if ($e == 1) {
        echo "<br><br>";
        echo "=============================";
        echo "<br><br>";
        generaOstacoli();
    }
}

function memorizzaPosizioneMuro(&$flowMuri, $x, $y)
{
    //Inserisco i valori in cui il flusso di muri proseguire (sotto forma di array)
    /*
    *   flowMuri[
    *     [x, y],
    *     [x, y],
    *     [x, y]
    *   ]
    */
    array_push($flowMuri, [$x, $y]);
    for ($i = 0; $i < sizeof($flowMuri); $i++) {
        echo "MEMORIZZAZIONE $i = ";
        echo $flowMuri[$i][0] . ":";
        echo $flowMuri[$i][1] . "<br>";
    }
}

function ancoraDaControllare(&$flowMuri, $x, $y)
{
    for ($i = 0; $i < sizeof($flowMuri); $i++) {
        if ($flowMuri[$i][0] == $x && $flowMuri[$i][1] == $y) {
            //se trovo corrispondenza con un muro già controllato non lo ricontrollo
            return false;
        }
    }
    return true;
}

function scorriMuri(&$flowMuri, $riga, $colonna)
{
    $counterSpostamenti = 0;
    $accentrato = 0;

    //ciclo tutto intorno al muro in cui mi trovo
    for ($x = -1; $x <= 1; $x++) {
        for ($y = -1; $y <= 1; $y++) {

            //Se trovo un ostacolo intorno al mio ostacolo (diverso da se stesso e che non ho ancora controllato partendo dal muro al bordo)
            if ($GLOBALS['$mappa'][$riga + $x][$colonna + $y] == OSTACOLO && !($x == 0 && $y == 0) && ancoraDaControllare($flowMuri, $riga + $x, $colonna + $y)) {
                $counterSpostamenti++;

                echo "Da " . $riga . ":" . $colonna . "= ";
                echo $GLOBALS['$mappa'][$riga][$colonna] . "<br>";

                //traslo i valori del precedente muro sul nuovo muro da cui riparterà il controllo
                $riga += $x;
                $colonna += $y;
                $x = -1;
                //alla fine del ciclo for viene eseguito $y++, so che non è molto bello da vedere, ma è la soluzione più rapida senza incasinare il codice
                $y = -2;

                echo "A " . $riga . ":" . $colonna . "= ";
                echo $GLOBALS['$mappa'][$riga][$colonna] . "<br>";
                echo "Counter Spostamenti= " . $counterSpostamenti . "<br>";

                //memorizzo il muro in cui mi sono appena spostato
                memorizzaPosizioneMuro($flowMuri, $riga, $colonna);
                //Qui setto il controllo che mi ritornerà la presenza di una barriera di muri
                $ultimoMuro = sizeof($flowMuri) - 1;


                //Se con l'ultimo movimento si è staccato (o per dirla in altro modo non è più sui bordi delle caselle disponibili)
                if (!($riga == 1 || $colonna == 1 || $colonna == (COLONNE - 2) || $riga == (RIGHE - 2))) {
                    echo "Si è spostato verso il centro<br>";
                    $accentrato = 1;
                }

                //Dopo essermi assicurato che si sia accentrato almeno una volta mi chiedo se Con l'ultimo movimento è tornato indietro?
                if ($accentrato == 1 && (($flowMuri[$ultimoMuro][0] == 1) || ($flowMuri[$ultimoMuro][0] == RIGHE - 2) || ($flowMuri[$ultimoMuro][1] == 1) || ($flowMuri[$ultimoMuro][1] == COLONNE - 2))) {
                    //Se si vuol dire che c'è un blocco nella mappa, non obbligatoriamente dannoso ma blocca future implementazioni di punti (ecc) che sono in punti non accessibili
                    
                    echo "Ultima riga del muro accentrato " . $flowMuri[$ultimoMuro][0] . "<br>";
                    echo "Ultima colonna del muro accentrato " . $flowMuri[$ultimoMuro][1] . "<br>";
                    echo "Possibile blocco<br>";
                    
                    return 1;
                }
            }
        }
    }
    return 0;
}

function esistonoPercorsiChiusi()
{
    //Ciclo tutta la tabella
    for ($riga = 1; $riga <= RIGHE - 2; $riga++) {
        for ($colonna = 1; $colonna <= COLONNE - 2; $colonna++) {

            //se trovo un ostacolo ai bordi esterni
            if ($GLOBALS['$mappa'][$riga][$colonna] == OSTACOLO && ($riga == 1 || $colonna == 1 || $riga == RIGHE - 2 || $colonna == COLONNE - 2)) {
                //creo un array che tenga traccia degli spostamenti che ho fatto
                //ogni volta che parto da un nuovo muro ai bordi azzero la mia struttura di controllo del flusso di muri
                $flowMuri = array();
                echo "<br>Muro ai bordi= " . $riga . ":" . $colonna . "<br>";

                //$counterSpostamenti = 0;

                //memorizzo muro di partenza
                memorizzaPosizioneMuro($flowMuri, $riga, $colonna);

                //dal muro esterno inizio a scorrere i muri ad esso consecutivi
                if (scorriMuri($flowMuri, $riga, $colonna)) {
                    echo "!!!!!!!!!!!!!!Blocco!!!!!!!!!!!!!!!<br>";
                    return true;
                } else {
                    echo "Nessun blocco<br>";
                }
            }
        }
    }
    //se non ho mai interrotto il ciclo non esistono passaggi chiusi
    return false;
}

function personaggio()
{
    $GLOBALS['$mappa'][1][1] = PERSONAGGIO;
}
function trofeo()
{
    $GLOBALS['$mappa'][RIGHE - 2][COLONNE - 2] = TROFEO;
}
/*
function ghost($x, $y)
{
    $GLOBALS['$mappa'][$x][$y] = GHOST;
}
*/

function riempiMatrice()
{
    bordiMappa();
    personaggio();
    trofeo();
    generaOstacoli();
}


?>
<!DOCTYPE html>
<html>

<head>
    <title>Recinto</title>
    <style>
        body {
            background-color: #d4d4d4;
            text-align: center;
        }

        * {
            font-family: 'Courier New', Courier, monospace;
        }

        h1 {
            border: dotted;
        }

        table {
            display: inline-block;
            border-collapse: collapse;
            font-size: 25px;
        }

        td {
            text-align: center;
            width: 30px;
            height: 30px;
            border: 1px solid black;
        }

        legend {
            display: block;
            padding-left: 10px;
            padding-right: 10px;
            border: none;
        }

        fieldset {
            display: block;
            margin: auto;
            border: dotted;
            width: 80%;
        }

        #commands{
            padding: 20px 10px;
            margin: auto;
            display: block;
            width: 50%;
        }


        input[type="button"]{
            height: 30px;
            border-radius: 5px;
        }

        #up, #down{
            width: 100%;
        }

        #right, #left{
            width: 40%;
        }
    </style>
    <script>
        function isNextContent(content) {
            //copio le variabili per non modificare i valori dell'oggetto
            //senza prima essere sicuro di poterlo spostare
            var e = pacman.e;
            var x = pacman.x;
            var y = pacman.y;

            switch (e) {
                //SX
                case 65:
                case 37: {
                    y--;
                    break;
                }
                //UP
                case 87:
                case 38: {
                    x--;
                    break;
                }
                //DX
                case 68:
                case 39: {
                    y++;
                    break;
                }
                //DOWN
                case 83:
                case 40: {
                    x++;
                    break;
                }
            }
            var contenutoCasellaDestinazione = document.getElementById(x + "_" + y).innerHTML;
            return (contenutoCasellaDestinazione == content);
        }

        //classe del mio oggetto pacman
        function Character(x, y) {
            this.x = x;
            this.y = y;
            this.vite = 3;
            //this.livello = 1; //servirebbe salvare il livello sul file esterno, così come il resto... 
            //in futuro potrei pensarci, ma prima voglio trovare un modo più 
            //professionale per creare giochi coi canvas e p5.js
            this.morti = 0;
            this.mosse = 0;
            this.e;
            this.updatePosition = function(e) {
                this.e = e;
                //Voglio solo che se sono gli ostacoli ad essere toccati muoia. I muri esterni non valgono
                if (!(isNextContent("*"))) {
                    if (!(isNextContent("#"))) {
                        this.mosse++;
                        document.getElementById(this.x + "_" + this.y).innerHTML = "";
                        if (isNextContent("P")) {
                            //this.livello++;
                            location.reload();
                        }
                        switch (e) {
                            //SX
                            case 65:
                            case 37: {
                                this.y--;
                                break;
                            }
                            //UP
                            case 87:
                            case 38: {
                                this.x--;
                                break;
                            }
                            //DX
                            case 68:
                            case 39: {
                                this.y++;
                                break;
                            }
                            //DOWN
                            case 83:
                            case 40: {
                                this.x++;
                                break;
                            }
                        }
                        document.getElementById(this.x + "_" + this.y).innerHTML = "C";
                    }
                } else {
                    this.morti++;
                    if (this.vite > 0) this.vite--;
                    if (this.vite == 0) {
                        document.getElementById("vite").style.color = "#FF0000";
                        alert("Hai perso scarsone XDXDXD");
                    }
                    //respawni all'inizio
                    document.getElementById(this.x + "_" + this.y).innerHTML = "";
                    document.getElementById("1_1").innerHTML = "C";
                    pacman.x = 1;
                    pacman.y = 1;
                }
                this.updateStats = function() {
                    document.getElementById("vite").innerHTML = this.vite;
                    //document.getElementById("livello").innerHTML = this.livello;
                    document.getElementById("morti").innerHTML = this.morti;
                    document.getElementById("mosse").innerHTML = this.mosse;
                }
            }
        }

        pacman = new Character(1, 1);


        document.body.addEventListener("keydown", event => {
            spostamento(event.keyCode);
        });

        function spostamento(keyCode){
            if (pacman.vite > 0) {
                pacman.updatePosition(keyCode);
                pacman.updateStats();
            }
        }
    </script>
</head>

<body>
<p>Puoi anche usare le frecce della tastiera o W A S D</p>
    <div id="commands">
        <input type="button" value="^" id="up" onclick="spostamento(38)">
        <input type="button" value="<" id="left" onclick="spostamento(37)">
        <input type="button" value=">" id="right" onclick="spostamento(39)">
        <input type="button" value="v" id="down" onclick="spostamento(40)">
    </div>
    <fieldset>
        <legend>Stats</legend>
        <p>Vite: <span id="vite">3</span></p>
        <!--<p>Livello: <span id="livello">1</span></p>-->
        <p>Morti: <span id="morti">0</span></p>
        <p>Mosse: <span id="mosse">0</span></p>
    </fieldset>
</body>

</html>