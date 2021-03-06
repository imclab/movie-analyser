<?php

/**
 * Movie Analyser
 * Given a list of films, looks them up on TheMovieDatabase to tell you
 * about what kind of films you like to watch.
 *
 * @author Daniel G Wood <https://github.com/danielgwood>
 */

require 'lib/MovieAnalyser.php';
require 'lib/Helper.php';

// Defines the location of the index
define('BASE_URL', 'http://movie-analyser.com/');

// Get the data
$set = MovieAnalyser::getSet($_GET['id']);

// Simple numbers
$totalMovies = $set['totalMovies'];
$totalRunTime = $set['totalRunningTime'];

// Best/worst genres
arsort($set['genres']);
$favouriteGenre = key(array_slice($set['genres'], 0, 1));

$topGenres = array_slice($set['genres'], 0, 5);

$leastFavouriteGenres = false;
if(count($set['genres']) > 5) {
    asort($set['genres']);
    $leastFavouriteGenres = array_slice($set['genres'], 0, 5);
}

// Collate years
$decades = array();
$years = array();

$currentYear = date('Y', $set['oldestFilm']['date']);
$lastYear = date('Y', $set['newestFilm']['date']);
while($currentYear <= $lastYear) {
    $years[$currentYear] = 0;
    $currentYear++;
}

foreach($set['releaseDates'] as $stamp) {
    $year = date('Y', $stamp);
    $decade = floor($year/10)*10;

    $years[$year]++;

    if(!isset($decades[$decade])) {
        $decades[$decade] = 0;
    }
    $decades[$decade]++;
}
ksort($years);
arsort($decades);
$favouriteDecade = key($decades);

// Favourite directors
arsort($set['directors']);
$directorsToShow = array_slice($set['directors'], 0, 5);

// Favourite cast
arsort($set['cast']);
$castToShow = array_slice($set['cast'], 0, min(count($set['cast']), 10));

// Ratings
$averageRating = (array_sum($set['ratings']) / count($set['ratings']));

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />

        <meta name="keywords" content="movies, cinema, films, analyser, statistics, infographic, tastes, genres, danielgwood, daniel, wood">
        <meta name="description" content="What kind of movies do you like? Who are your favourite actors, actresses and directors? Find out now!">
        <meta property="og:title" content="Movie Analyser">
        <meta property="og:description" content="What kind of movies do you like? Who are your favourite actors, actresses and directors? Find out now!">
        <meta itemprop="name" content="Movie Analyser">
        <meta itemprop="description" content="What kind of movies do you like? Who are your favourite actors, actresses and directors? Find out now!">

        <meta property="og:type" content="website" />
        <meta property="og:image" content="assets/img/social-thumb.png" />
        <meta property="og:site_name" content="movie-analyser.com" />
        <meta property="fb:admins" content="512686422" />
        <meta itemprop="image" content="assets/img/social-thumb.png">

        <title>My Movie Collection</title>

        <link href='http://fonts.googleapis.com/css?family=Holtwood+One+SC' rel='stylesheet' />
        <link rel="stylesheet" href="assets/css/all.css" />
    </head>

    <body>
        <a href="https://github.com/danielgwood/movie-analyser"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_red_aa0000.png" alt="Fork me on GitHub"></a>

        <header>
            <h1>My Movie Collection</h1>
        </header>

        <section>
            <div class="grid_4 size">
                <h2>Viewing time</h2>

                <p>There are <span class="stat"><?php echo Helper::escape($totalMovies); ?> movies</span> in my collection, with a total running time of <span class="stat"><?php echo number_format($totalRunTime); ?></span> minutes.</p>
                <p>That&apos;s <span class="stat"><?php echo Helper::minsToDays($totalRunTime); ?> days</span> of back&ndash;to&ndash;back viewing!</p>
            </div>

            <div class="grid_8 genres">
                <h2><?php echo Helper::escape($favouriteGenre); ?> is my favourite genre</h2>


                <div id="genre-chart" class="grid_4 alpha"></div>

                <div class="grid_4 omega">
                    <?php if($leastFavouriteGenres): ?>
                    <h3>I'm not so keen on&hellip;</h3>
                        <ul>
                            <?php foreach($leastFavouriteGenres as $genre => $count): ?><li><?php echo Helper::escape($genre); ?></li><?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                    <p>My tastes aren&apos;t very diverse!</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section>
            <div class="grid_4 directors">
                <h2>Directors</h2>
                <?php if(count($directorsToShow) > 0): ?>
                <ol>
                <?php

                foreach($directorsToShow as $director => $numberOfFilms) {
                    echo '<li>' . Helper::escape($director) . ' (' . $numberOfFilms . ' films)</li>';
                }

                ?>
                </ol>
                <?php endif; ?>
            </div>

            <div class="grid_8 actors">
                <h2>Actresses/actors</h2>
                <?php if(count($castToShow) > 0): ?>
                <ol class="two-column">
                <?php

                foreach($castToShow as $actor => $numberOfFilms) {
                    echo '<li>' . Helper::escape($actor) . '</li>';
                }

                ?>
                </ol>
                <?php endif; ?>
            </div>
        </section>

        <section class="years">
            <h2>I really like the <?php echo $favouriteDecade; ?>s</h2>

            <div id="years-chart"></div>

            <div class="grid_6">
                <h3>Oldest film</h3>
                <p><?php echo Helper::escape($set['oldestFilm']['title']) . ' (' . date('Y', $set['oldestFilm']['date']) . ')'; ?></p>
            </div>

            <div class="grid_6">
                <h3>Newest film</h3>
                <p><?php echo Helper::escape($set['newestFilm']['title']) . ' (' . date('Y', $set['newestFilm']['date']) . ')'; ?></p>
            </div>
        </section>

        <section class="ratings">
            <h2>Average rating: <?php echo Helper::escape(round($averageRating, 1)); ?>/10</h2>

            <div class="movie">
                <img src="<?php echo $set['bestFilm']['poster']; ?>" class="movie-poster">
                <h3>Best film</h3>
                <h4><?php echo Helper::escape($set['bestFilm']['title']); ?></h4>
                <span class="movie-rating"><?php echo Helper::escape($set['bestFilm']['rating']); ?>/10</span>
            </div>

            <div class="movie">
                 <img src="<?php echo $set['worstFilm']['poster']; ?>" class="movie-poster">
                <h3>Worst film</h3>
                <h4><?php echo Helper::escape($set['worstFilm']['title']); ?></h4>
                <span class="movie-rating"><?php echo Helper::escape($set['worstFilm']['rating']); ?>/10</span>
            </div>

            <p>&nbsp;</p>
            <?php if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == BASE_URL): ?>
                <p>Think your friends can do better?</p>

                <div class="share-buttons">
                    <a href="#share"
                      class="fb-share"
                      onclick="
                        window.open(
                          'https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href),
                          'facebook-share-dialog',
                          'width=626,height=436');
                        return false;">
                        <i></i>Share on Facebook
                    </a>

                    <a href="https://twitter.com/share" class="twitter-share-button" data-text="Check out my movie collection!" data-via="danielgwood" data-size="large" data-count="none" data-hashtags="movieanalyser">Tweet</a>
                </div>

            <?php else: ?>
                <p>Think your taste is better?</p>
                <a class="cta" href="<?php echo BASE_URL; ?>">Try it yourself!</a>
            <?php endif; ?>
        </section>

        <footer>
            <p>Data courtesy of <a href="http://www.themoviedb.org/"><img src="assets/img/tmdb.png" alt="TheMovieDb.org" /></a>, generated using <a href="http://movie-analyser.com">Movie Analyser</a>.</p>
            <p>Icons by <a href="http://dribbble.com/SAMURAY">Nikolay Kuchkarov</a>. Additional thanks to <a href="https://github.com/glamorous">Jonas De Smet</a>.</p>
        </footer>

        <script src="http://d3js.org/d3.v3.min.js"></script>
        <script src="assets/js/d3.yearChart.js"></script>
        <script src="assets/js/d3.pieChart.js"></script>
        <script>

        var genres = [
            <?php

            for($i = 0; $i < count($topGenres); $i++) {
                $isLast = $i == count($topGenres)-1;

                echo '{"label": "' . Helper::escape(key($topGenres)) . '", "value": ' . ((int)current($topGenres)) . '}' . ((!$isLast) ? ',' : '') . "\n";

                next($topGenres);
            }

            ?>
        ];
        d3.pieChart.init('#genre-chart', 300, 170, genres);

        var years = [
        <?php

            for($i = 0; $i < count($years); $i++) {
                $isLast = $i == count($years)-1;

                echo '{"label": "' . Helper::escape(key($years)) . '", "value": ' . ((int)current($years)) . '}' . ((!$isLast) ? ',' : '') . "\n";

                next($years);
            }

            ?>
        ];
        d3.yearChart.init('#years-chart', 940, 180, years);

        </script>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-15405999-2', 'movie-analyser.com');
          ga('send', 'pageview');

        </script>
    </body>
</html>