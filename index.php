<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Touhou Scarlet Adventure!</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="styles/styles.css">
</head>
<body background="resources/bg.png">
  <!-- Header -->
  <header>
    <div class="container">
      <h1>Touhou Scarlet Adventure!</h1>
      <nav>
        <ul>
          <li><a href="#about">About</a></li>
          <li><a href="#screenshots">Screenshots</a></li>
          <li><a href="#download">Download</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Main Content -->
  <main>
    <!-- About Section -->
    <section id="about" class="section">
      <div class="container">
        <h2>About the Game</h2>
        <p>
          Welcome to the world of <strong>Touhou Scarlet Adventure</strong>! Embark on an epic journey filled with thrilling battles,
          magical encounters, and unforgettable characters. Whether you're a seasoned gamer or new to the genre, this game
          offers something for everyone.
        </p>
      </div>
    </section>

    <!-- Screenshots Section -->
    <section id="screenshots" class="section">
      <div class="container">
        <h2>Screenshots</h2>
        <div class="screenshot-container">
          <div class="screenshot-scroll">
            <img src="https://images5.alphacoders.com/755/755672.jpg" alt="Screenshot 1">
            <img src="https://images4.alphacoders.com/720/720684.jpg" alt="Screenshot 2">
            <img src="https://images8.alphacoders.com/761/761886.jpg" alt="Screenshot 3">
            <img src="https://images6.alphacoders.com/869/thumb-1920-869292.jpg" alt="Screenshot 4">
            <img src="https://pixelz.cc/wp-content/uploads/2018/12/touhou-reimu-hakurei-uhd-4k-wallpaper.jpg" alt="Screenshot 5">
          </div>
        </div>
      </div>
    </section>
    <!-- Records Section -->
    <section id="records" class="section">
      <div class="container">
        <div class="records-container">
          <h2>Leaderboard</h2>
          <div class="scrollable-section">
              <?php
              $api_url = 'http://' . $_SERVER['HTTP_HOST'] . '/api/get-records.php';
              
              try {
                  $response = file_get_contents($api_url);
                  
                  if($response === false) {
                      throw new Exception('Failed to connect to the API');
                  }
                  
                  $data = json_decode($response, true);
                  
                  if(json_last_error() !== JSON_ERROR_NONE) {
                      throw new Exception('Invalid API response format');
                  }
                  
                  if($data['status'] !== 'success') {
                      echo '<div class="error-message">'.htmlspecialchars($data['message']).'</div>';
                  } else {
                      if(empty($data['records'])) {
                          echo '<div class="status-message">No records available</div>';
                      } else {
                        $rank = 1;
                        foreach($data['records'] as $record) {
                            echo '
                            <div class="record-item">
                                <span class="rank">#'.$rank.'</span>
                                <span class="username">'.htmlspecialchars($record['username']).'</span>
                                <span class="score">'.number_format($record['score']).' points</span>
                            </div>';
                            $rank++;
                        }
                      }
                  }
                  
              } catch(Exception $e) {
                  echo '<div class="error-message">Error: '.htmlspecialchars($e->getMessage()).'</div>';
              }
              ?>
          </div>
        </div>
      </div>
    </section>
    <!-- Download Section -->
    <section id="download" class="section">
      <div class="container">
        <h2>Download</h2>
        <p>Ready to dive into the adventure? Download the game now!</p>
        <a target="_blank" href="https://github.com/ManoKiku/Touhou-Scarlet-Adventure/releases" class="download-button">Download Now <i class="fas fa-download"></i></a>
      </div>
    </section>
  </main>
  
  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="footer-content">
        <p>&copy; 2025 Touhou Scarlet Adventure! All rights reserved.</p>
        <div class="social-links">
          <a target="_blank" href="https://discord.com/users/549927558339100674" aria-label="Discord"><i class="fab fa-discord"></i></a>
          <a target="_blank" href="https://github.com/ManoKiku" aria-label="GitHub"><i class="fab fa-github"></i></a>
        </div>
      </div>
    </div>
  </footer>

  <!-- JavaScript for Scrollable Screenshots -->
  <script>
    const scrollContainer = document.querySelector(".screenshot-scroll");

    scrollContainer.addEventListener("wheel", (e) => {
      e.preventDefault();
      scrollContainer.scrollLeft += e.deltaY;
    });
  </script>
</body>
</html>