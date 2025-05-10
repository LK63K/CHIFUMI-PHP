<?php

// PARTIE 
    function showMainMenu(){
        echo "\n==hey bienvenue sur le jeu shifoumi fais ton choix avec des chiffres==\n";
        echo "\n=== Menu principal ===\n \n";
        echo "1. commencer une nouvelle partie\n";
        echo "2. consulter l'historique des parties précédentes\n";
        echo "3. consulter les statistiques des parties précédentes\n";
        echo "4. quitter\n";
}

    function startNewGame() {
        echo "\n=== Nouvelle partie ===\n";

    
        $cancel = readline(" tape entrée pour continuer, \n a tout moment tu peux taper 'q' pour revenir au menu principal: ");
        if (strtolower($cancel) === 'q') {
        echo "Retour au menu principal...\n";
        return;
        }

    
        $playerChoice = getPlayerChoice();
        if ($playerChoice === false) {
            return;
        }

    
        $cpuChoice = getCpuChoice();

    
        $result = theWinner($playerChoice, $cpuChoice);

    
        echo "\nChoix du joueur: $playerChoice\n";
        echo "Choix du CPU: $cpuChoice\n";
        echo "Résultat: $result\n";
    
        saveGameResult($playerChoice, $cpuChoice, $result);
    
        do {
            $nextGame = readline("Rejouer ? (oui/non): ");
            if (strtolower($nextGame) === 'oui') {
                startNewGame();
                return;
            } elseif (strtolower($nextGame) === 'non') {
                echo "Retour au menu principal...\n";
                return;
            } else {
                echo "Veuillez répondre par 'oui' ou 'non'\n";
            }
        } while (true);
}
    
    function getPlayerChoice(){
        do{
            echo "\nChoisis entre pierre, feuille ou ciseau\n";
            $choise = readline("Entre ton choix : ");
            if(strtolower($choise) === 'q') {
                echo "Retour au menu principal...\n";
                return false;
            }
            if(in_array($choise, ['pierre', 'feuille', 'ciseau'])){
                return $choise;
            }
            echo "\nChoix invalide ! Choisis entre pierre, feuille ou ciseau.\n";
        }while(true);
    }

    function getCpuChoice(){
        $cpuChoise = ['pierre', 'feuille', 'ciseau'];
        return $cpuChoise[array_rand($cpuChoise)];
    }

    function theWinner($player,$cpu){
        if ($player === $cpu) return "égalité";

        $winConditions = [
            'pierre' => 'ciseau',
            'ciseau' => 'feuille',
            'feuille' => 'pierre',

        ];
        return $winConditions [$player] === $cpu ? "le joueur a gagné GG" : "l'ordi a gagné bahaha";
    }

// HISTORIQUE

    function saveGameResult($player, $cpu, $result) {
        $date = date('Y-m-d H:i:s');
        $historyFile = __DIR__ . "/gameHystory.csv";
        $statsFile = __DIR__ . "/stats.csv";
        
        if (!file_exists($historyFile)) {
            touch($historyFile);
            chmod($historyFile, 0666);
            $file = fopen($historyFile, "w");
            fputcsv($file, ["date", "player", "cpu", "result"]);
            fclose($file);
        }

        if (!file_exists($statsFile)) {
            touch($statsFile);
            chmod($statsFile, 0666);
            $file = fopen($statsFile, "w");
            fputcsv($file, ["nombre de parties jouées", "victoires du joueur", "taux de victoire", "main la plus gagnante"]);
            fputcsv($file, [0, 0, 0, ""]);
            fclose($file);
        }
        
        
        $file = fopen($historyFile, "a");
        if ($file === false) {
            echo "Erreur: Impossible d'ouvrir le fichier historique\n";
            return;
        }
        fputcsv($file, [$date, $player, $cpu, $result]);
        fclose($file);

        
        $totalGames = 0;
        $playerWins = 0;
        $mainStats = [
            'pierre' => ['jouées' => 0, 'victoires' => 0],
            'feuille' => ['jouées' => 0, 'victoires' => 0],
            'ciseau' => ['jouées' => 0, 'victoires' => 0]
        ];

        $file = fopen($historyFile, "r");
        if ($file !== false) {
            $isFirstLine = true;
            while (($line = fgetcsv($file)) !== false) {
                if ($isFirstLine) {
                    $isFirstLine = false;
                    continue;
                }
                
                $totalGames++;
                $playerChoice = $line[1];
                $mainStats[$playerChoice]['jouées']++;
                
                if ($line[3] === "le joueur a gagné GG") {
                    $playerWins++;
                    $mainStats[$playerChoice]['victoires']++;
                }
            }
            fclose($file);
        }

        
        $tauxVictoire = $totalGames > 0 ? round(($playerWins / $totalGames) * 100, 2) : 0;

        
        $mainPlusGagnante = '';
        $maxVictoires = 0;
        foreach ($mainStats as $main => $stats) {
            if ($stats['jouées'] > 0 && $stats['victoires'] > $maxVictoires) {
                $maxVictoires = $stats['victoires'];
                $mainPlusGagnante = $main;
            }
        }

        
        $file = fopen($statsFile, "w");
        if ($file === false) {
            echo "Erreur: Impossible d'ouvrir le fichier statistiques\n";
            return;
        }

        fputcsv($file, ["nombre de parties jouées", "victoires du joueur", "taux de victoire", "main la plus gagnante"]);
        fputcsv($file, [$totalGames, $playerWins, $tauxVictoire, $mainPlusGagnante]);
        fclose($file);
    }


    function showGameHistory(){
        $fileName = __DIR__ . "/gameHystory.csv";

        if (!file_exists($fileName)){
            echo "aucune partie enregistrée. \n";
            return;
        }

        $file = fopen($fileName, "r");
        if ($file === false) {
            echo "Erreur lors de l'ouverture du fichier historique.\n";
            return;
        }

        $isFirstLine = true;
        
        echo "\n=== Historique des parties ===\n";
        echo "+---------------------+--------+--------+-------------------+\n";
        echo "| Date                | Player | CPU    | Result           |\n";
        echo "+---------------------+--------+--------+-------------------+\n";
        
        while (($line = fgetcsv($file)) !== false){
            if ($isFirstLine){
                $isFirstLine = false;
                continue;
            }
            
            if (count($line) >= 4) {
                printf(
                    "| %-19s | %-6s | %-6s | %-15s |\n",
                    $line[0],
                    $line[1],
                    $line[2],
                    $line[3]
                );
            }
        }
        
        echo "+---------------------+--------+--------+-------------------+\n";
        fclose($file);
        
        readline("\nAppuie sur Entrée pour retourner au menu principal...");
    }

    function showStatistics() {
        $filename = __DIR__ . "/stats.csv";
    
        if (!file_exists($filename)) {
            echo "Aucune partie enregistrée.\n";
            return;
        }
    
        $file = fopen($filename, "r");
    
        if (!$file) {
            echo "Erreur lors de l'ouverture du fichier.\n";
            return;
        }
    
        
        $headers = fgetcsv($file);
        $stats = fgetcsv($file);
        
        if ($stats === false) {
            echo "Aucune statistique disponible.\n";
            fclose($file);
            return;
        }
        
        echo "\n=== Statistiques des parties ===\n";
        
        
        if (count($stats) >= 2) {
            echo "Nombre de parties jouées : " . $stats[0] . "\n";
            echo "Victoires du joueur : " . $stats[1] . "\n";
            
            
            if ($stats[0] > 0) {
                $tauxVictoire = round(($stats[1] / $stats[0]) * 100, 2);
                echo "Taux de victoire : " . $tauxVictoire . "%\n";
            }
            
    
            if (count($stats) >= 4 && !empty($stats[3])) {
                echo "Main la plus gagnante : " . $stats[3] . "\n";
            }
        } else {
            echo "Format de statistiques invalide.\n";
        }
        
        fclose($file);
        
        readline("\nAppuie sur Entrée pour retourner au menu principal...");
    }
    


function runGame() {
    do {
        showMainMenu();
        $choice = readline("Entre ton choix: ");
        switch($choice) {
            case "1":
                startNewGame();        
                break;
            case "2":
                showGameHistory();
                break;
            case "3":
                showStatistics();
                break;
            case "4":
                echo "Au revoir !\n";
                exit;
            default:
                echo "Choix invalide veuillez reesayez (seulement les choix 1,2,3,4 sont valides)\n";
                readline("Appuyez sur Entrée pour continuer...");
        }
    } while (true);
}

runGame();
      
?>