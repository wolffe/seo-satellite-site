/**
https://codepen.io/ciprian/pen/pMbXzb
https://github.com/GoogleChrome/lighthouse/blob/master/docs/scoring.md
https://docs.google.com/spreadsheets/d/1Cxzhy5ecqJCucdf1M0iOzM8mIxNc7mmx107o5nj38Eo/edit#gid=0
https://github.com/GoogleChrome/lighthouse/issues/8864
/**/

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('init-lighthouse').addEventListener('click', function (event) {
        event.preventDefault();

        document.getElementById('seo-audit').innerHTML = '<img src="https://www.dublinseo.net/seo/loader.gif" alt="">';

        var request = new XMLHttpRequest(),
            plainUrl = document.getElementById('init-lighthouse-url').value,
            encodedUrl = encodeURIComponent(plainUrl);


        var siteUrl = 'https://www.dublinseo.net/seo/SeoReport.php?url=' + document.getElementById('init-lighthouse-url').value;

        request.open('GET', siteUrl, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.onload = function (response) {
            if (this.status >= 200 && this.status < 400) {
                document.getElementById('seo-audit').innerHTML = this.response;

                Array.from(document.querySelectorAll('.progress')).forEach(function(element) {
                	var options = {};
                	['progress', 'roundStroke', 'zeroTick', 'label'].forEach(function (item) {
                		if (element.dataset[item]) {
                			options[item] = element.dataset[item]
                			if (options[item] === 'true') options[item] = true
                			if (options[item] === 'false') options[item] = false
                		}
                	})
                	var progress = new Progress(element, options)
                })
            } else {
                // Response error
            }
        };
        request.onerror = function () {
            // Connection error
        };
        request.send('url=' + siteUrl);




        fetch("https://www.googleapis.com/pagespeedonline/v5/runPagespeed?screenshot=true&strategy=mobile&url=" + encodedUrl)
          .then(function(response) {
            return response.json();
          })
          .then(function(myJson) {
            var firstContentfulPaintTitle = myJson.lighthouseResult.audits['first-contentful-paint'].title;
            var firstContentfulPaintDisplayValue = myJson.lighthouseResult.audits['first-contentful-paint'].displayValue;
            document.querySelector('#first-contentful-paint span').innerText = firstContentfulPaintDisplayValue;

            var speedIndexTitle = myJson.lighthouseResult.audits['speed-index'].title;
            var speedIndexDisplayValue = myJson.lighthouseResult.audits['speed-index'].displayValue;
            document.querySelector('#speed-index span').innerText = speedIndexDisplayValue;

            var timeToInteractiveTitle = myJson.lighthouseResult.audits['interactive'].title;
            var timeToInteractiveDisplayValue = myJson.lighthouseResult.audits['interactive'].displayValue;
            document.querySelector('#time-to-interactive span').innerText = timeToInteractiveDisplayValue;

            var firstMeaningfulPaintTitle = myJson.lighthouseResult.audits['first-meaningful-paint'].title;
            var firstMeaningfulPaintDisplayValue = myJson.lighthouseResult.audits['first-meaningful-paint'].displayValue;
            document.querySelector('#first-meaningful-paint span').innerText = firstMeaningfulPaintDisplayValue;

            var firstCpuIdleTitle = myJson.lighthouseResult.audits['first-cpu-idle'].title;
            var firstCpuIdleDisplayValue = myJson.lighthouseResult.audits['first-cpu-idle'].displayValue;
            document.querySelector('#first-cpu-idle span').innerText = firstCpuIdleDisplayValue;

            var estimatedInputLatencyTitle = myJson.lighthouseResult.audits['estimated-input-latency'].title;
            var estimatedInputLatencyDisplayValue = myJson.lighthouseResult.audits['estimated-input-latency'].displayValue;
            document.querySelector('#estimated-input-latency span').innerText = estimatedInputLatencyDisplayValue;

         // console.log(myJson.lighthouseResult.audits['final-screenshot'].details.data);
         // document.querySelector('.final-screenshot').src = myJson.lighthouseResult.audits['final-screenshot'].details.data;

            //console.log(JSON.stringify(myJson));
          });
    });
});
