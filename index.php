<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

// $connectionString = "DefaultEndpointsProtocol=https;AccountName=" . getenv('ACCOUNT_NAME') . ";AccountKey=" . getenv('ACCOUNT_KEY');
$connectionString = "DefaultEndpointsProtocol=https;AccountName=rizki;AccountKey=geQbFuedfCzw6N41oZ902UwQpa+apjvJ1e9SKy3ofwansxFxaSaLoVkA9bFZYrbN81qswlKigafg8K1Oe4K66A==";

$blobClient = BlobRestProxy::createBlobService($connectionString);

$createContainerOptions = new CreateContainerOptions();
$createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
$createContainerOptions->addMetaData("key1", "value1");
$createContainerOptions->addMetaData("key2", "value2");

$containerName = "rizki" . generateRandomString();

try {
    $blobClient->createContainer($containerName, $createContainerOptions);
} catch (ServiceException $e) {
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code . ": " . $error_message . "<br />";
} catch (InvalidArgumentTypeException $e) {
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code . ": " . $error_message . "<br />";
}
if (isset($_POST['submit'])) {
    $temp = explode(".", $_FILES["images"]["name"]);
    $fileToUpload =  $containerName . '.' . end($temp);
    echo '<pre>';
    if (move_uploaded_file($_FILES['images']['tmp_name'], $fileToUpload)) {

        echo "File is valid, and was successfully uploaded.\n";

        $myfile = fopen($fileToUpload, "r") or die("Unable to open file!");
        fclose($myfile);

        echo "Uploading BlockBlob: " . PHP_EOL;
        echo $fileToUpload;

        $content = fopen($fileToUpload, "r");

        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
        header('location: index.php?containerName=' . $containerName);
    } else {
        header('location: index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rz Image Analyzer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body class="bg-dark text-light">
    <div class="jumbotron jumbotron-fluid text-dark">
        <div class="container">
            <h1 class="display-4">Rz Image Analyzer</h1>
            <p class="lead">Automatically analyze image description using computer vision</p>
        </div>
    </div>

    <div class="container container-fluid mb-4">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="container container-fluid">
                    <div class="custom-file mb-2">
                        <input type="file" class="custom-file-input" id="customFile" name="images">
                        <label class="custom-file-label" for="customFile">Choose Image</label>
                    </div>
                    <input type="submit" name="submit" value="Analyze" class="btn btn-primary form-control">
                </div>
            </div>
            <div class="row">
                <div class="container-fluid mt-4">
                    <?php
                    if (!empty($_GET['containerName'])) {
                        $containerName = $_GET['containerName'];

                        $listBlobsOptions = new ListBlobsOptions();


                        do {
                            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);

                            foreach ($result->getBlobs() as $blob) {
                                echo '<img src=' . $blob->getUrl() . ' id="images" class="img-fluid">';
                            }

                            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
                        } while ($result->getContinuationToken());
                    }
                    ?>
                </div>
            </div>
            <div class="row" id="result">
                <div class="col-md-12">
                    <h2 id="captions" class="text-center text-light"></h2>
                </div>
            </div>
        </form>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script type='text/javascript' src='jquery.min.js'></script>
<script>
    $("#result").hide();
</script>
<script>
    $('#customFile').on('change', function() {
        var fileName = $(this).val().replace('C:\\fakepath\\', " ");
        $(this).next('.custom-file-label').html(fileName);
    })
</script>
<script src="https://static.line-scdn.net/liff/edge/2.1/sdk.js"></script>
<script src="liff-starter.js"></script>
<?php
if (!empty($_GET['containerName'])) {
?>
    <script type="text/javascript">
        $("#result").show();

        var subscriptionKey = "9ad4ecc469ff4586bbd65510225844c2";

        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";

        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };

        var sourceImageUrl = document.getElementById("images").src;

        $.ajax({
                url: uriBase + "?" + $.param(params),

                // Request headers.
                beforeSend: function(xhrObj) {
                    xhrObj.setRequestHeader("Content-Type", "application/json");
                    xhrObj.setRequestHeader(
                        "Ocp-Apim-Subscription-Key", subscriptionKey);
                },

                type: "POST",
                // Request body.
                data: '{"url": ' + '"' + sourceImageUrl + '"}',
            })

            .done(function(data) {
                // Show formatted JSON on webpage.
                $("#captions").text(data['description']['captions'][0]['text']);

                if (!liff.isInClient()) {
                    console.log('Web is opened in external browser');
                } else {
                    liff.sendMessages([{
                        'type': 'text',
                        'text': "Analyzed Image : " + data['description']['captions'][0]['text']
                    }]).then(function() {
                        window.alert('Image Analyzed!');
                    }).catch(function(error) {
                        window.alert('Error sending message: ' + error);
                    });
                }
            })

            .fail(function(jqXHR, textStatus, errorThrown) {
                // Display error message.
                var errorString = (errorThrown === "") ? "Error. " :
                    errorThrown + " (" + jqXHR.status + "): ";
                errorString += (jqXHR.responseText === "") ? "" :
                    jQuery.parseJSON(jqXHR.responseText).message;
                alert(errorString);
            });
    </script>
<?php
}
?>

</html>