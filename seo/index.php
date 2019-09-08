<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

require_once "ApiClient.php";

if (isset($_POST['url'])) {
    $insights = new ApiClient("https://ckp.ie"); // "AIzaSyBIGeuUoIOXgBx--P8x7KiuNDSt7bhABgY"

    echo '<pre><code>';
    print_r($insights);
    echo '</code></pre>';
    echo 'aaa<br>';

    //$url = $_POST['url'];

    //echo getSeoReport($url);
}
?>
<!doctype html>
<html>
<head>
<title>Design Bricks SEO Report Demo</title>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Google+Sans:400,700&display=swap">
<style>
body {
    background-color: #fff;
    color: #24292e;
    font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Helvetica, Arial, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol;
    font-family: "Google Sans";
    font-size: 16px;
    line-height: 1.5;
}
code.small {
    font-family: SFMono-Regular, Consolas, Liberation Mono, Menlo, monospace;
    font-size: 12px;
    color: #0366d6;
}

.seo-form-wrapper,
.seo-audit-wrapper {
    width: 960px;
    margin: 0 auto;
    word-wrap: break-word;
}
.seo-form-wrapper input[type="url"] {
    font-family: inherit;
    font-size: inherit;
    border: 1px solid #bdc3c7;
    padding: 8px;
    border-radius: 3px;
    width: 100%;
}
.seo-form-wrapper input[type="submit"] {
    font-family: inherit;
    font-size: 18px;
    font-weight: 400;
    background-color: #1abc9c;
    color: #ffffff;
    display: inline-block;
    padding: 16px 24px;
    border: 0 none;
    border-radius: 3px;
    cursor: pointer;
}
.seo-form-wrapper input[type="submit"]:hover {
    background-color: #16a085;
}
.seo-audit-snippet {
    width: 720px;
    margin: 24px;
}

.is-good {
    color: #1abc9c;
}
.is-bad {
    color: #e74c3c;
}
.is-ugly {
    color: #e67e22;
}

#scores {
  display: flex;
  flex-wrap: wrap;
}
#scores div {
  flex-basis: 31%;
  margin: 1%;
}

.progress {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: calc(25% - 2rem);
  height: 183px;
  width: 183px;
  min-width: 183px;
  position: relative;
  margin: 48px auto;
}
.progress__track, .progress__progress {
  height: 100%;
  width: 100%;
  position: absolute;
  stroke-width: 18;
  fill: none;
}
.progress__track {
  stroke: #ccc;
}
.progress__progress {
  stroke: #e74c3c;
}
.progress__indicator {
  position: absolute;
  left: 50%;
  top: 50%;
  height: 50%;
  width: 0;
  -webkit-transform-origin: 0% 0%;
          transform-origin: 0% 0%;
  -webkit-transform: rotate(0deg);
          transform: rotate(0deg);
}
.progress__indicator-hand {
  height: 35px;
  width: 35px;
  position: absolute;
  bottom: -10px;
  left: calc(50% - 20px);
  background-color: #e74c3c;
  border: 3px solid #fff;
  border-radius: 50%;
}
.progress__label {
  color: #444;
  text-align: center;
}
.progress__text {
  display: block;
  font-size: 4rem;
  line-height: 0.9;
}
.progress__percent {
  font-size: 0.8rem;
  text-transform: uppercase;
}

</style>
</head>
<body>

<div class="seo-form-wrapper">
    <h1>Design Bricks SEO Audit</h1>

    <form method="post">
        <p>
            <label for="init-lighthouse-url">Site URL</label>
            <br><input type="url" name="url" id="init-lighthouse-url">
            <br><small>e.g. https://www.example.com/</small>
        </p>
        <p>
            <input type="submit" value="Run Audit" id="init-lighthouse">
        </p>
    </form>
</div>

<div id="seo-audit"></div>

<script src="https://use.fontawesome.com/releases/v5.10.0/js/all.js"></script>
<script src="js/lighthouse.js"></script>
<script src="js/progress.js"></script>
</body>
</html>
