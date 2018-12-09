<nav class="navbar" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <div class="navbar-item">
            <img src="/images/logo.png" alt="<?php echo NOME_SITO ?>" style="margin-right: 10px"/>
            <b><?php echo NOME_SITO ?> <span class="tag is-warning">ALPHA</span></b>
        </div>
        <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>
    <div class="navbar-menu">
        <?php
        if (isset($sessione)):
            ?>
            <div class="navbar-start">
                <a href="/dashboard.php" class="navbar-item">Dashboard</a>
                <a href="/dashboard.php" class="navbar-item">Attestati</a>
                <a href="/dashboard.php" class="navbar-item">Corsi</a>
                <a href="/account/supporto/" class="navbar-item">Supporto</a>
                <?php
                $gestione = false;
                foreach ($permessi as $permesso) {
                    if ($permesso['richiede_pannello_gestione'] && $permesso['reale']) {
                        $gestione = true;
                        break;
                    }
                }

                if ($gestione):
                    ?>
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                            Gestione
                        </a>
                        <div class="navbar-dropdown">
                            <div class="columns">
                                <div class="column">
                                    <div class="navbar-item">
                                        <b>UTENTI</b>
                                    </div>
                                    <a class="navbar-item" href="/gestione/utenti/ricerca.php">
                                        Ricerca utenti
                                    </a>
                                    <a class="navbar-item" href="/gestione/utenti/crea.php">
                                        Crea utente
                                    </a>
                                    <a class="navbar-item" href="/gestione/gruppi/">
                                        Gestione gruppi
                                    </a>
                                </div>
                                <div class="column">
                                    <div class="navbar-item">
                                        <b>PRESENZE & ATTIVITÀ</b>
                                    </div>
                                    <a class="navbar-item">
                                        Timeline generale
                                    </a>
                                    <a class="navbar-item">
                                        Gestione presenze
                                    </a>
                                    <a class="navbar-item">
                                        Gestione attività
                                    </a>
                                    <a class="navbar-item">
                                        Ricerca badge
                                    </a>
                                    <a href="" class="navbar-item">
                                        Gestione Maker Space
                                    </a>
                                </div>
                                <div class="column">
                                    <div class="navbar-item">
                                        <b>GENERALE</b>
                                    </div>
                                    <a class="navbar-item" href="/gestione/log/logs.php">
                                        Log del gestionale
                                    </a>
                                    <a class="navbar-item">
                                        Firewall
                                    </a>
                                    <a class="navbar-item">
                                        Query al database
                                    </a>
                                </div>
                            </div>
                            <div class="columns">
                                <div class="column">
                                    <div class="navbar-item">
                                        <b>CORSI</b>
                                    </div>
                                    <a class="navbar-item">
                                        Crea un corso
                                    </a>
                                    <a class="navbar-item">
                                        Ricerca corsi
                                    </a>
                                </div>
                                <div class="column"></div>
                                <div class="column"></div>
                            </div>
                        </div>
                    </div>
                <?php
                endif;

                //if ($permessi['socio']['reale']):
                ?>
                <div class="navbar-item has-dropdown is-hoverable">
                    <a class="navbar-link">
                        Associazione
                    </a>
                    <div class="navbar-dropdown">
                        <div class="columns">
                            <div class="column">
                                <div class="navbar-item">
                                    <b>ASSEMBLEA</b>
                                </div>
                                <a class="navbar-item">
                                    Assemblee
                                </a>
                                <a class="navbar-item">
                                    Quota associativa
                                </a>
                                <a class="navbar-item">
                                    Rimborsi
                                </a>
                            </div>
                            <div class="column">
                                <div class="navbar-item">
                                    <b>CONSIGLIO DIRETTIVO</b>
                                </div>
                                <a class="navbar-item">
                                    Sedute
                                </a>
                                <a class="navbar-item">
                                    Segreteria
                                </a>
                                <a class="navbar-item">
                                    Tesoreria
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                //endif;
                ?>
            </div>
            <div class="navbar-end">
                <div class="navbar-item has-dropdown is-hoverable">
                    <a class="navbar-link">
                        <?php echo $utente->nome . ' ' . $utente->cognome ?>
                    </a>
                    <div class="navbar-dropdown">
                        <a href="/account/impostazioni.php" class="navbar-item">Impostazioni</a>
                        <a href="/account/notifiche.php" class="navbar-item">Notifiche</a>
                        <a href="/logout.php" class="navbar-item">Disconnettiti</a>
                    </div>
                </div>
            </div>
        <?php
        else:
            ?>
            <div class="navbar-start">
                <a href="/" class="navbar-item">Registrazione</a>
                <a href="/login.php" class="navbar-item">Accesso</a>
            </div>
        <?php
        endif;
        ?>
    </div>
</nav>
