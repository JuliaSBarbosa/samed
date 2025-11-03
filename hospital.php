<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAMED - Unidades de Saúde</title>
    <link rel="stylesheet" href="estilos/style.css">
    <link rel="icon" href="img/logo.png" type="image/png">
</head>

<body>
    <header class="topo">
        <div class="logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>

        <nav class="menu">
            <a href="index.php">INÍCIO</a>
            <span class="divisor">|</span>
            <a href="perfil.php">MEU PERFIL</a>
            <span class="divisor">|</span>
            <a href="dependentes.php">DEPENDENTES</a>
            <span class="divisor">|</span>
            <a href="hospital.php" class="ativo">UNIDADES DE SAÚDE</a>
        </nav>

        <a href="sair.php" class="botao-sair">
            <img src="img/sair.svg" alt="Sair">
            SAIR
        </a>
    </header>

    <main>
        <h2 class="titulo">HOSPITAIS PRÓXIMOS</h2>

        <div class="hospital-list-container">
            <ul class="hospital-list">
                
                <li class="hospital-card">
                    <img src="img/logo-santa-casa-itapira.png" alt="Logo Santa Casa de Itapira" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>SANTA CASA DE ITAPIRA</h3>
                            <span class="city-tag">Itapira - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Cel.+Jose+Pires,138,Itapira,SP" target="_blank">
                                R. Cel. José Píres, 138 - Centro, Itapira - SP, 13970-060
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3863-1122</p>
                    </div>
                </li>

                <li class="hospital-card">
                    <img src="img/logo-hospital-municipal-itapira.png" alt="Logo Hospital Municipal de Itapira" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL MUNICIPAL DE ITAPIRA</h3>
                            <span class="city-tag">Itapira - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong>
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Farmaceutico+Pires+de+Godoi,313,Itapira,SP" target="_blank">
                                R. Farm. Píres de Godói, 313 - Centro, Itapira - SP, 13970-190
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3843-1222</p>
                    </div>
                </li>
                
                <li class="hospital-card">
                    <img src="img/logo-hospital-sao-francisco.png" alt="Logo Hospital São Francisco" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL SÃO FRANCISCO</h3>
                            <span class="city-tag">Mogi Guaçu - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong> 
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Inacio+Franco+Alves,561,Mogi+Guacu,SP" target="_blank">
                                R. Inácio Franco Alves, 561 - Parque Cidade Nova, Mogi Guaçu - SP, 13845-420
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3851-8000</p>
                    </div>
                </li>

                <li class="hospital-card">
                    <img src="img/logo-santa-casa-mogiguacu.png" alt="Logo Hospital Santa Casa" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL SANTA CASA</h3>
                            <span class="city-tag">Mogi Guaçu - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong>
                            <a href="https://www.google.com/maps/search/?api=1&query=Rua+Chico+de+Paula,608,Mogi+Guacu,SP" target="_blank">
                                R. Chico de Paula, 608 - Centro, Mogi Guaçu - SP, 13840-005
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3861-1313</p>
                    </div>
                </li>

                <li class="hospital-card">
                    <img src="img/logo-hospital-22-outubro.png" alt="Logo Hospital 22 de Outubro" class="hospital-logo">
                    <div class="hospital-info">
                        <div class="hospital-title">
                            <h3>HOSPITAL 22 DE OUTUBRO</h3>
                            <span class="city-tag">Mogi Mirim - SP</span>
                        </div>
                        <p>
                            <strong>Endereço:</strong>
                            <a href="https://www.google.com/maps/search/?api=1&query=Av+22+de+Outubro,733,Mogi+Mirim,SP" target="_blank">
                                Av. 22 de Outubro, 733 - Jardim Santa Helena, Mogi Mirim - SP, 13806-050
                            </a>
                        </p>
                        <p><strong>Telefone:</strong> (19) 3814-3400</p>
                    </div>
                </li>

            </ul>
        </div>
    </main>
    <footer>
        <div class="footer-logo">
            <img src="img/logo.svg" alt="Logo SAMED">
            <h1>SAMED</h1>
        </div>
        <p>DESENVOLVIDO POR GRUPO AINDA SEM NOME.</p>
        <div class="lojas">
            <img src="img/appstore.png" alt="App Store">
            <img src="img/googleplay.png" alt="Google Play">
        </div>
    </footer>
</body>

</html>