<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Busca em arquivos PDF</title>
    <style>
        body {
            background-color: #222;
            color: #fff;
            font-family: Arial, sans-serif;
        }

        h1 {
            text-align: center;
            margin-top: 50px;
        }

        form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin-top: 50px;
        }

        label {
            font-size: 24px;
            margin-bottom: 20px;
        }

        input[type=text] {
            font-size: 24px;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            background-color: #444;
            color: #fff;
            border-radius: 5px;
            outline: none;
            width: 500px;
            text-align: center;
        }

        button[type=submit] {
            font-size: 24px;
            padding: 10px 20px;
            background-color: #4CAF50;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type=submit]:hover {
            background-color: #3e8e41;
        }

        ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            grid-gap: 20px;
            margin-top: 50px;
        }

        li {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        li:hover {
            transform: translateY(-5px);
        }

        a {
            color: #333;
            font-size: 18px;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .pdf-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        #pdf-viewer {
            width: 100%;
            height: 1000px;
            margin-top: 50px;
            display: none;
        }

    </style>
</head>
<body>
    <h1>Busca em arquivos PDF</h1>

    <form method="post" id="pdf-form" onsubmit="return validarTermo();">
        <label for="termo">Digite o termo a ser buscado:</label>
        <input type="text" name="termo" id="termo" placeholder="Digite o termo">
        <button type="submit" onclick="if(document.getElementById('termo').value.length >= 4)">Buscar</button>
    </form>
<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $termo = $_POST["termo"];
        $diretorio = ".";
        $arquivos = shell_exec("find $diretorio -name '*.pdf'");
        $arquivos = explode("\n", trim($arquivos));
        $resultados = [];

        foreach ($arquivos as $arquivo) {
            if (!empty($arquivo)) {
                $nome_arquivo = basename($arquivo, ".pdf");
                $caminho_arquivo = dirname($arquivo);
                $arquivo_txt = "$caminho_arquivo/$nome_arquivo.txt";

                if (!file_exists($arquivo_txt)) {
                    $comando = "pdftotext '$arquivo' '$arquivo_txt'";
                    shell_exec($comando);
                }

                $comando = "grep -irn '$termo' '$arquivo_txt'";
                $result = shell_exec($comando);

                if (!empty($result)) {
                  $nome_arquivo = substr(basename($arquivo, ".pdf"), 0, 60);
                  $resultados[] = "<li><a href=\"$arquivo\" onclick=\"showPdf('$arquivo'); return false;\">" . $nome_arquivo . "</a></li>";
                }

            }
        }

        if (count($resultados) > 0) {
            echo "<ul>";
            echo implode("", $resultados);
            echo "</ul>";
        } else {
            echo "<p>Nenhum resultado encontrado para o termo \"$termo\"</p>";
        }
    }
?>
<iframe id="pdf-iframe" name="pdf" style="display: none; min-height: 1000px; width: 100%;"></iframe>


<script>
    function scrollToBottom() {
      window.scrollTo(0, document.body.scrollHeight);
    }

    function validarTermo() {
      var termo = document.getElementById("termo").value.trim();
      if (termo.length < 4) {
        alert("O termo deve ter pelo menos 4 caracteres");
        return false;
      }
      return true;
    }

    function showPdf(url) {
      var pdfIframe = document.getElementById("pdf-iframe");
      pdfIframe.style.display = "block";
      pdfIframe.src = url;
      scrollToBottom();
    }

    var pdfIframe = document.getElementById("pdf-iframe");
    pdfIframe.onload = function() {
      var pdfDocument = pdfIframe.contentWindow.document;
      var downloadButton = pdfDocument.querySelector(".download");
      if (downloadButton) {
        downloadButton.remove();
      }
    };

    document.addEventListener('keydown', function(event) {
      // Desativa o comportamento padrão do "Ctrl + C" ou "Command + C"
      if (event.metaKey || event.ctrlKey) {
        event.preventDefault();
      }
    });

    document.addEventListener('contextmenu', function (e) {
      e.preventDefault();
    });

</script>
</body>
</html>
