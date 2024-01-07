<?php
// Layout & Dantenbank
require_once 'layout/layout.php';
require_once 'database.php';

session_start();
// SESSION
if(!isset($_SESSION['userid'])) {
    header('Location: auth/login.php');
}
// Neuer Eintraag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['new']) && $_GET['new'] === '1') {
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $url = isset($_POST['url']) ? $_POST['url'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    if (empty($title) || empty($url)) {
        echo '<div class="alert alert-danger" role="alert">Please fill in all fields</div>';
    } else {
        // Prüfen ob URL existiert
        $url = convertYouTubeUrl($url);
        $statement = $pdo->prepare("INSERT INTO videos (url, title, description) VALUES (:url, :title, :description)");
        $result = $statement->execute(array(
            'url' => $url,
            'title' => $title,
            'description' => $description,
        ));
        if ($result) {
            header('Location: index.php');
        } else {
            echo '<div class="alert alert-danger" role="alert">Error adding video. Please try again.</div>';
        }
    }
}
// Schreibe Url zu embedUrl um
function convertYouTubeUrl($url) {
    $videoId = '';
    if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $videoId = $matches[1];
    }
    if (!empty($videoId) && strpos($url, 'embed') === false) {
        $url = "https://www.youtube.com/embed/{$videoId}?si=xk1r101Gc7FL-m5h";
    }
    return $url;
}
// Videos holen
$query = "SELECT * FROM videos";
$stmt = $pdo->query($query);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>vidbase</title>
        <!-- Alert Verstecken -->
        <style>.hidden {display: none !important;}</style>    
    </head>
    <body data-bs-theme="dark">
        <form action="auth/logout.php">
            <button type="submit" class="btn btn-outline-light m-2">Logout</button>
        </form>
    <div class="container d-flex justify-content-center align-items-center gap-2 mt-1">
        <h1 class="fw-bold text-center">vidbase</h1>
        <img src="assets/logo.jpg" alt="Logo" class="rounded-3" height="80" draggable="false">
    </div>
    <div class="container-fluid p-5 pt-0">
        <div class="row">
            <div class="col-md-3 w-25">
                <div class="mt-5 mb-4" id="sidenav" style="height: 50px;">
                <!-- SUCHE -->
                <form action="" id="searchForm">
    <input class="form-control border border-2 border-light rounded-3 p-2" list="searchfunc" id="search" placeholder="Suche...">
    <datalist id="searchfunc">
        <?php 
        // Datalist mit einträgen füllen für Auto Comlete
        $videoTitles = array_column($videos, 'title');
        foreach ($videoTitles as $title): ?>
            <option value="<?= $title ?>">
        <?php endforeach; ?>
    </datalist>
</form>
<script>
// View anpassen wenn suche postiv aus fällt
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('searchForm').addEventListener('input', function() {
        var searchInput = document.getElementById('search').value.toLowerCase();
        var videoTitles = <?php echo json_encode($videoTitles); ?>;
        var index = videoTitles.findIndex(function(title) {
            return title.toLowerCase() === searchInput;
        });
        if (index !== -1) {
            var buttons = document.querySelectorAll('.list-group-item');
            if (buttons.length > index) {
                buttons[index].click();
            }
        }
    });
});
</script>
        </div>
            <div class="border border-2 border-light rounded-4 pt-3 p-3 overflow-auto" id="sidenav" style="height: 50vw">
                <button type="button" class="btn btn-outline-light w-100 mb-3 sticky-top bg-dark" data-toggle="modal" data-target="#youtubeModal">+</button><br>
                    <div class="modal fade" id="youtubeModal" tabindex="-1" role="dialog" aria-labelledby="youtubeModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="youtubeModalLabel">Neuer Eintrag</h5>
                                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                </div>
                                    <div class="modal-body">
                                        <!-- Formular für neuen eintrag -->
                                        <form id="new" action="?new=1" method="post">
                                                <div class="form-floating mb-3 m-3">
                                                    <input type="text" class="form-control" id="title" placeholder="Title" name="title">
                                                    <label for="title">Titel</label>
                                                </div>
                                                <div class="form-floating mb-3 m-3">
                                                    <input type="text" class="form-control" id="url" placeholder="URL" name="url" required>
                                                    <label for="url">URL</label>
                                                </div>
                                                <div class="form-floating mb-3 m-3">
                                                    <textarea class="form-control" placeholder="Description" id="description" name="description" style="height: 500px"></textarea>
                                                    <label for="description">Notizen</label>
                                                </div>
                                            <button type="submit" class="btn btn-outline-light ms-3">Hinzufügen</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div id="sidenav">
                        <!-- Navigations Punkte für Einträge erstellen -->
                        <?php foreach ($videos as $video): ?>
                            <a class="icon-link link-body-emphasis link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover fs-5 ms-3 list-group-item list-group-item-action" data-video_id="<?= $video['video_id']?>" data-url="<?= $video['url'] ?>" data-title="<?= $video['title'] ?>" data-description="<?= $video['description'] ?>" href="#" style="text-decoration: none; color: inherit;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-youtube" viewBox="0 0 16 16"><path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408z"/></svg>
                                <span><?= $video['title'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    <script>
                        $(document).ready(function () {
                            // AJAX Daten überliefrung an Formualre
                            displayVideoContent($('.list-group-item:first'));
                            $('.list-group-item').click(function () {
                                displayVideoContent($(this));
                            });
                            function displayVideoContent(button) {
                                // Get data attributes
                                var title = button.data('title');
                                var url = button.data('url');
                                var description = button.data('description');
                                var video_id = button.data('video_id')
                                // Display content
                                $('#video-title').text(title);
                                $('#video-iframe').attr('src', url);
                                $('#video-description').text(description);
                                $('#video-video_id').text(video_id);
                            }
                            // URL Kopieren
                            $('#copyUrlBtn').click(function () {
                                var urlToCopy = $('#video-iframe').attr('src');
                                var videoId = getYouTubeVideoId(urlToCopy);
                                var watchUrl = 'https://www.youtube.com/watch?v=' + videoId;
                                var tempInput = $('<input>');
                                $('body').append(tempInput);
                                tempInput.val(watchUrl).select();
                                document.execCommand('copy');
                                tempInput.remove();
                                var floatingAlert = $('#floatingAlert');
                                floatingAlert.removeClass('hidden');
                                floatingAlert.text('URL Kopiert')
                                setInterval(function () {
                                    floatingAlert.addClass('hidden');
                                }, 3400);});
                                // Eintrag Löschen
                                $('#deleteEntryBtn').click(function () {
                                var videoId = $('#video-video_id').text();
                                $('#deleteVideoId').val(videoId);
                                $('#deleteModal').modal('show');});
                                function getYouTubeVideoId(url) {
                                var regex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
                                var match = url.match(regex);
                                return match ? match[1] : null;
                                }
                                // Eintrag Bearbeiten
                                $('#editEntryBtn').click(function () {
                                    var title = $('#video-title').text();
                                    var url = $('#video-iframe').attr('src');
                                    var description = $('#video-description').text();
                                    var video_id = $('#video-video_id').text();
                                    $('#editTitle').val(title);
                                    $('#editUrl').val(url);
                                    $('#editDescription').val(description);
                                    $('#video_id').val(video_id);
                                    $('#editModal').modal('show');
                                });});
                    </script>
                </div><br>
            </div>
        </div>
    <div class="col-md-9 w-75 mt-5">
        <div class="d-flex">
            <h1 class="me-auto" id="video-title"></h1>
                <div>                    
                <div id="floatingAlert" class="hidden alert alert-success float-alert" role="alert"></div>
                    <button type="button" class="btn btn-outline-light m-2" id="copyUrlBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-share-fill" viewBox="0 0 16 16"><path d="M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.499 2.499 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5"/></svg>
                    </button>
                    <button type="button" class="btn btn-outline-light m-2" id="editEntryBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cursor-text" viewBox="0 0 16 16"><path d="M5 2a.5.5 0 0 1 .5-.5c.862 0 1.573.287 2.06.566.174.099.321.198.44.286.119-.088.266-.187.44-.286A4.165 4.165 0 0 1 10.5 1.5a.5.5 0 0 1 0 1c-.638 0-1.177.213-1.564.434a3.49 3.49 0 0 0-.436.294V7.5H9a.5.5 0 0 1 0 1h-.5v4.272c.1.08.248.187.436.294.387.221.926.434 1.564.434a.5.5 0 0 1 0 1 4.165 4.165 0 0 1-2.06-.566A4.561 4.561 0 0 1 8 13.65a4.561 4.561 0 0 1-.44.285 4.165 4.165 0 0 1-2.06.566.5.5 0 0 1 0-1c.638 0 1.177-.213 1.564-.434.188-.107.335-.214.436-.294V8.5H7a.5.5 0 0 1 0-1h.5V3.228a3.49 3.49 0 0 0-.436-.294A3.166 3.166 0 0 0 5.5 2.5.5.5 0 0 1 5 2m-.704 9.29"/></svg>
                    </button>
                    <button type="button" class="btn btn-outline-light m-2" id="deleteEntryBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16"><path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0"/></svg>
                    </button>
                    <div id="deleteModal" class="modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <!-- Löschen Modal -->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Achtung</h5>
                                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button></button>
                                </div>
                                <div class="modal-body">
                                    <p>Willst du diesen eintrag wirklich löschen?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Nein</button>
                                    <form id="deleteForm" method="post" action="">
                                        <input type="hidden" id="deleteVideoId" name="video_id_del" value="">
                                        <button type="submit" class="btn btn-danger">Ja</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <!-- Bearbeitungs Modal -->
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel">Eintrag Bearbeiten</h5>
                                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                </div>
                                    <div class="modal-body">
                                        <form id="editForm" action="?edit=1" method="post">
                                        <input type="hidden" class="form-control" id="video_id" placeholder="e" name="newVideoId">
                                            <div class="form-floating mb-3 m-3">
                                                <input type="text" class="form-control" id="editTitle" placeholder="e" name="newTitle">
                                                <label for="editTitle">Title</label>
                                            </div>
                                            <div class="form-floating mb-3 m-3">
                                                <input type="text" class="form-control" id="editUrl" placeholder="URL" name="newUrl" required>
                                                <label for="editUrl">URL</label>
                                            </div>
                                            <div class="form-floating mb-3 m-3">
                                                <textarea class="form-control" placeholder="Description" id="editDescription" name="newDescription" style="height: 500px"></textarea>
                                                <label for="editDescription">Notizen</label>
                                            </div>
                                            <button type="submit" class="btn btn-outline-light ms-3">Save Changes</button>
                                        </form>
                                    <?php
                                    // Löschen an DB überliefern
                                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                        if (isset($_POST['video_id_del'])) {
                                            $video_id_del = $_POST['video_id_del'];
                                            $sql = "DELETE FROM videos WHERE video_id = :video_id_del";
                                            try {
                                                $stmt = $pdo->prepare($sql);
                                                $stmt->bindParam(':video_id_del', $video_id_del, PDO::PARAM_STR);
                                                $stmt->execute();
                                                echo '<script>setTimeout(function() {window.location.href = "index.php";}, 1000); // 1 seconds delay</script>';
                                            } catch (PDOException $e) {
                                            }
                                        }
                                    }
                                    // Eintrag Aktuallisieren
                                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                        if (
                                            isset($_POST['newTitle']) &&
                                            isset($_POST['newUrl']) &&
                                            isset($_POST['newDescription']) &&
                                            isset($_POST['newVideoId'])
                                        ) {
                                            $newTitle = $_POST['newTitle'];
                                            $newUrl = $_POST['newUrl'];
                                            $newDescription = $_POST['newDescription'];
                                            $newVideoId = $_POST['newVideoId'];
                                            $sql = "UPDATE videos SET title = :newTitle, url = :newUrl, description = :newDescription WHERE video_id = :newVideoId";
                                            try {
                                                $stmt = $pdo->prepare($sql);
                                                $stmt->bindParam(':newTitle', $newTitle, PDO::PARAM_STR);
                                                $stmt->bindParam(':newUrl', $newUrl, PDO::PARAM_STR);
                                                $stmt->bindParam(':newDescription', $newDescription, PDO::PARAM_STR);
                                                $stmt->bindParam(':newVideoId', $newVideoId, PDO::PARAM_STR);            
                                                $stmt->execute();
                                                echo '<script>setTimeout(function() {window.location.href = "index.php";}, 1000); // 1 seconds delay</script>';
                                            } catch (PDOException $e) {
                                                echo "Fehler beim Aktualisieren des Videos: " . $e->getMessage();
                                            }
                                        } else {
                                            echo "Nicht alle erforderlichen Formularfelder sind gesetzt.";
                                        }
                                    }
                                    ?>
                </div>
            </div>
        </div>
    </div>
        </div>
            </div>
                <div class="mt-3">
                    <iframe id="video-iframe" class="w-100 rounded-3" width="" height="600"  title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        <h4>Notizen  <button type="button" class="btn btn-outline-light m-2" onclick="clickButton()"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cursor-text" viewBox="0 0 16 16"><path d="M5 2a.5.5 0 0 1 .5-.5c.862 0 1.573.287 2.06.566.174.099.321.198.44.286.119-.088.266-.187.44-.286A4.165 4.165 0 0 1 10.5 1.5a.5.5 0 0 1 0 1c-.638 0-1.177.213-1.564.434a3.49 3.49 0 0 0-.436.294V7.5H9a.5.5 0 0 1 0 1h-.5v4.272c.1.08.248.187.436.294.387.221.926.434 1.564.434a.5.5 0 0 1 0 1 4.165 4.165 0 0 1-2.06-.566A4.561 4.561 0 0 1 8 13.65a4.561 4.561 0 0 1-.44.285 4.165 4.165 0 0 1-2.06.566.5.5 0 0 1 0-1c.638 0 1.177-.213 1.564-.434.188-.107.335-.214.436-.294V8.5H7a.5.5 0 0 1 0-1h.5V3.228a3.49 3.49 0 0 0-.436-.294A3.166 3.166 0 0 0 5.5 2.5.5.5 0 0 1 5 2m-.704 9.29"/></svg></button></h4>
                    <script>
                        // Edit knopf drücken & in Notizen Feld Taben
                        function clickButton() {
                            var button = document.getElementById('editEntryBtn');
                            if (button) {
                                button.click();
                                for (var i = 0; i < 4; i++) {
                                    var tabEvent = new KeyboardEvent('keydown', {
                                        key: 'Tab',
                                        keyCode: 9,
                                        which: 9,
                                    });
                                    document.dispatchEvent(tabEvent);
                                }
                            }
                        }
                    </script>
                <p id="video-video_id" style="display: none;"></p>
                <p id="video-description"></p>
            </div>
        </div>
    </div>
</body>
</html>
<!-- It all ends here -->