<?php
function isAlive($url) {
    set_time_limit(0);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 7200);
    curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2);
    curl_exec ($ch);
    $int_return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);

    $validCodes = [200, 301, 302, 304];

    if (in_array($int_return_code, $validCodes)) {
        return ['HTTP_CODE' => $int_return_code, 'STATUS' => true];
    } else {
        return ['HTTP_CODE' => $int_return_code, 'STATUS' => false];
    }
}

function findGoogleAnalytics($grabbedHtml){
    $pos = strrpos($grabbedHtml, 'GoogleAnalyticsObject');

    return ($pos > 0) ? true : false;
}

function findGoogleTagManager($grabbedHtml) {
    $pos = strrpos($grabbedHtml, 'GTM-');

    return ($pos > 0) ? true : false;
}

function isDNSReachable($url) {
    $dnsReachable = checkdnsrr(addScheme($url));
    return $dnsReachable == false ? false : true;
}

function addScheme($url, $scheme = 'http://') {
    return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
}

function getWordCounts($phrases) {
    $counts = [];

    foreach ($phrases as $phrase) {
        $words = explode(' ', strtolower($phrase));

        $grammar = ["a", "an", "the", "shall", "should", "can", "could", "will", "would", "am", "is", "are", "we", "us", "has", "have", "had", "not", "yes", "no", "true", "false", "with", "to", "your", "more", "and", "in", "out", "login", "logout", "sign", "up", "coming", "going", "now", "then", "about", "contact", "my", "you", "of", "our"];
        $words = array_diff($words, $grammar);

        foreach ($words as $word) {
            if (!empty(trim($word))) {
                $word = preg_replace("#[^a-zA-Z\-]#", '', $word);
                if (isset($counts[$word])) {
                    $counts[$word] += 1;
                } else {
                    $counts[$word] = 1;
                }
            }
        }
    }

    return $counts;
}

function brokenLinkTester($link) {
    set_time_limit(0);
    $handle = curl_init($link);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if ((int) $httpCode === 200) {
        return true;
    } else {
        return false;
    }
}

function getBrokenLinkCount($anchors) {
    $count = 0;
    $blinks = [];
    foreach ($anchors as $a) {
        array_push($blinks, $a->getAttribute('href'));
    }
    if (!empty($blinks)) {
        foreach ($blinks as $ln) {
            $res = brokenLinkTester($ln);
            if ($res) {
                $count++;
            }
        }
    }

    return $count;
}

function checkForFiles($filename, $url) {
    $handle = curl_init("https://www." . $url . "/" . $filename);
    curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if ((int) $httpCode === 200) {
        return true;
    } else {
        return false;
    }
}

function imageAltText($imgs) {
    $totImgs = 0;
    $totAlts = 0;
    $diff = 0;

    foreach ($imgs as $im) {
        $totImgs++;
        if (!empty($im->getAttribute('alt'))) {
            $totAlts++;
        }
    }

    return ['totImgs' => $totImgs, 'totAlts' => $totAlts, 'diff' => ($totImgs - $totAlts)];
}

function grabHTML($url) {
    set_time_limit(0);
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
    if(strtolower(parse_url($url, PHP_URL_SCHEME)) === 'https') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }
    $str = curl_exec($ch);
    curl_close($ch);

    return ($str) ? $str : false;
}

function formatCheckLinks($link) {
    $cssFile = '';

    if (strpos($cssFile, '?') !== false) {
        $cssFile = substr($link, strrpos($link, '/'), strrpos($link, '?') - strrpos($link, '/'));
    } else {
        $cssFile = substr($link, strrpos($link, '/'));
    }

    if (strpos($cssFile, '.min.') !== false) {
        return true;
    } else {
        return false;
    }
}

function jsFinder($jsExists) {
    $push['jsCount'] = 0;
    $push['jsMinCount'] = 0;
    $push['jsNotMinFiles'] = [];

    if (!empty($jsExists)) {
        foreach ($jsExists as $ce) {
            $push['jsCount']++;
            if (formatCheckLinks($ce->getAttribute('src'))) {
                $push['jsMinCount']++;
            } else {
                array_push($push['jsNotMinFiles'], $ce->getAttribute('src'));
            }
        }
    }

    return $push;
}

function cssFinder($cssExists) {
    $push['cssCount'] = 0;
    $push['cssMinCount'] = 0;
    $push['cssNotMinFiles'] = [];

    if (!empty($cssExists)) {
        foreach ($cssExists as $ce) {
            $push['cssCount']++;
            if (formatCheckLinks($ce->getAttribute('href'))) {
                $push['cssMinCount']++;
            } else {
                array_push($push['cssNotMinFiles'], $ce->getAttribute('href'));
            }
        }
    }

    return $push;
}

function stripHtmlTags($str) {
    $str = preg_replace('/(<|>)\1{2}/is', '', $str);
    $str = preg_replace([
        '@<head[^>]*?>.*?</head>@siu',
        '@<style[^>]*?>.*?</style>@siu',
        '@<script[^>]*?.*?</script>@siu',
        '@<noscript[^>]*?.*?</noscript>@siu',
    ], '', $str);

    $str = replaceWhitespace($str);
    $str = html_entity_decode($str);
    $str = strip_tags($str);

    return $str;
}

function replaceWhitespace($str) {
    $result = $str;

    foreach (["  ", "   ", " \t",  " \r", " \n", "\t\t", "\t ", "\t\r", "\t\n", "\r\r", "\r ", "\r\t", "\r\n", "\n\n", "\n ", "\n\t", "\n\r",] as $replacement) {
        $result = str_replace($replacement, $replacement[0], $result);
    }

    return $str !== $result ? replaceWhitespace($result) : $result;
}






/**
 * This method need to call from your source class file to generate SEO Report
 */
function getSeoReport($url) {
    $htmlInfo = [];
    $htmlInfo['dnsReachable'] = isDNSReachable($url);

	$isAlive = isAlive($url);

	if ($isAlive['STATUS'] == true) {
		$grabbedHTML = grabHTML($url);
		$htmlInfo = array_merge($htmlInfo, getSiteMeta($grabbedHTML, $url));
		$htmlInfo['isAlive'] = true;
	} else {
		$htmlInfo['isAlive'] = false;
	}
	$htmlInfo['url'] = $url;
	$reqHTML = getReadyHTML($htmlInfo);

	return $reqHTML;
}


/**
 * This function used to get meta and language information from HTML
 * @param string $grabbedHTML : This is HTML string
 * @return array $htmlInfo : This is information grabbed from HTML
 */
function getSiteMeta($grabbedHTML, $url) {
	$html = new DOMDocument();

	libxml_use_internal_errors(true);
	$html->loadHTML($grabbedHTML);
	libxml_use_internal_errors(false);

	$xpath = new DOMXPath($html);

	$htmlInfo = [];

	$langs = $xpath->query('//html');
	foreach ($langs as $lang) {
		$htmlInfo['language'] = $lang->getAttribute('lang');
	}
	$metas = $xpath->query('//meta');
	foreach ($metas as $meta) {
		if ($meta->getAttribute('name')){
			$htmlInfo[$meta->getAttribute('name')] = $meta->getAttribute('content');
		}
	}

	$favicon = $xpath->query("//link[@rel='icon']");
	if (!empty($favicon)) {
		foreach ($favicon as $fav) {
			$htmlInfo[$fav->getAttribute('rel')] = $fav->getAttribute('href');
		}
	}

	$title = $xpath->query("//title");
	foreach ($title as $tit){
		$htmlInfo['titleText'] = $tit->textContent;
	}

	$htmlInfo = array_change_key_case($htmlInfo, CASE_LOWER);
	$onlyText = stripHtmlTags($grabbedHTML);

	if (!empty($onlyText)) {
		$onlyText = array(trim($onlyText));
		$count = getWordCounts($onlyText);
		$grammar = ["a"=>"", "an"=>"", "the"=>"", "shall"=>"", "should"=>"", "can"=>"", "could"=>"",
					"will"=>"", "would"=>"", "am"=>"", "is"=>"", "are"=>"", "we"=>"", "us"=>"", "has"=>"",
					"have"=>"", "had"=>"", "not"=>"", "yes"=>"", "no"=>"", "true"=>"", "false"=>"", "with"=>"",
					"to"=>"", "your"=>"", "more"=>"", "and"=>"", "in"=>"", "out"=>"", "login"=>"", "logout"=>"",
					"sign"=>"", "up"=>"", "coming"=>"", "going"=>"", "now"=>"", "then"=>"", "about"=>"",
					"contact"=>"", "my"=>"", "you"=>"", "go"=>"", "close"=>"", ""=>"", "of"=>"", "our"=>""];

		$count = array_diff_key($count, $grammar);

		arsort($count, SORT_DESC | SORT_NUMERIC);

		$htmlInfo['wordCount'] = $count;
		$htmlInfo['wordCountMax'] = array_slice($count, 0, 5, true);
	}

	$h1headings = $xpath->query("//h1");
	$index = 0;
	foreach ($h1headings as $h1h) {
		$htmlInfo['h1'][$index] = trim(strip_tags($h1h->textContent));
		$index++;
	}

	$h2headings = $xpath->query("//h2");
	$index = 0;
	foreach ($h2headings as $h2h) {
		$htmlInfo['h2'][$index] = trim(strip_tags($h2h->textContent));
		$index++;
	}

	$htmlInfo["robots"] = checkForFiles("robots.txt", $url);
	$htmlInfo["sitemap"] = checkForFiles("sitemap.xml", $url);
	$htmlInfo["sitemap_index"] = checkForFiles("sitemap_index.xml", $url);

	$htmlInfo["brokenLinkCount"] = 0;
	$anchors = $xpath->query("//a");
	if(!empty($anchors)){
// 		$htmlInfo["brokenLinkCount"] = getBrokenLinkCount($anchors);
	}

	$htmlInfo["images"] = [];
	$imgs = $xpath->query("//img");
	if(!empty($imgs)){
		$htmlInfo["images"] = imageAltText($imgs);
	}

	$htmlInfo["googleAnalytics"] = findGoogleAnalytics($grabbedHTML);
	$htmlInfo["googleTagManager"] = findGoogleTagManager($grabbedHTML);

	$htmlInfo["css"] = [];
	$cssExists = $xpath->query("//link[@rel='stylesheet']");
	$htmlInfo["css"] = array_merge ($htmlInfo["css"], cssFinder($cssExists));

	$htmlInfo["js"] = [];
	$jsExists = $xpath->query("//script[contains(@src, '.js')]");
	$htmlInfo["js"] = array_merge ($htmlInfo["js"], jsFinder($jsExists));

	return $htmlInfo;
}

function get_percentage($total, $number) {
    if ((int) $total > 0) {
        return round($number/($total/100), 2);
    } else {
        return 0;
    }
}

function getReadyHTML($htmlInfo) {
    $html = '';
    $score = 0;

    // Build score // 0/13
    if ($htmlInfo['isAlive'] === true) { ++$score; }
    if (isset($htmlInfo['titletext'])) { ++$score; }
    if (isset($htmlInfo['description'])) { ++$score; }
    if (!empty($htmlInfo['wordCountMax'])) { ++$score; }
    if (isset($htmlInfo['h1'])) { ++$score; }
    if (isset($htmlInfo['h2'])) { ++$score; }
    if ((int) $htmlInfo['robots'] === 200) { ++$score; }
    if ((int) $htmlInfo['sitemap'] === 200 || (int) $htmlInfo['sitemap_index'] === 200) { ++$score; }
    if (isset($htmlInfo['shortcut icon']) || isset($htmlInfo['icon'])) { ++$score; }
    if ((bool) $htmlInfo['googleAnalytics'] === true || (bool) $htmlInfo['googleTagManager'] === true) { ++$score; }

    $html .= '<div class="seo-audit-wrapper">';
        if ($htmlInfo['isAlive'] === true) {
            $html .= '<h1><i class="far fa-fw fa-check-circle is-good"></i> Site Report for ' . addScheme($htmlInfo['url'], 'http://') . '</h1>';

            $html .= '<div class="progress" data-progress="' . get_percentage(13, $score) . '"></div>';

            $html .= '<div id="scores">
                <div id="first-contentful-paint">First Contentful Paint <span><i class="fas fa-spin fa-cog"></i></span></div>
                <div id="speed-index">Speed Index <span><i class="fas fa-spin fa-cog"></i></span></div>
                <div id="time-to-interactive">Time to Interactive <span><i class="fas fa-spin fa-cog"></i></span></div>
                <div id="first-meaningful-paint">First Meaningful Paint <span><i class="fas fa-spin fa-cog"></i></span></div>
                <div id="first-cpu-idle">First CPU Idle <span><i class="fas fa-spin fa-cog"></i></span></div>
                <div id="estimated-input-latency">Estimated Input Latency <span><i class="fas fa-spin fa-cog"></i></span></div>
            </div>';
            // $html .= '<img src="https://placehold.it/320x480?text=Loading+Screenshot..." class="final-screenshot" alt="Screenshot">';

            $html .= '<h2>Search Engine Site Appearance</h2>
            <p>';
                if (isset($htmlInfo['titletext'])) {
                    $html .= '<b>' . $htmlInfo['titletext'] . '</b>
					<br><small>The title of your page has a length of ' . strlen($htmlInfo['titletext']) . ' characters. Most search engines will truncate titles to 70 characters.</small>';
				} else {
					$html .= '<b><i class="far fa-fw fa-times-circle"></i> No title</b>
                    <br><small class="is-bad">Your page does not have a title.</small>';
				}
            $html .= '</p>
            <p>';
                if (isset($htmlInfo['description'])) {
                    $html .= '<b>' . $htmlInfo['description'] . '</b>
					<br><small>The description of your page has a length of ' . strlen($htmlInfo['description']) . ' characters. Most search engines will truncate descriptions to 160 characters.</small>';
				} else {
					$html .= '<b><i class="far fa-fw fa-times-circle"></i> No title</b>
                    <br><small class="is-bad">Your page does not have a description.</small>';
				}
            $html .= '</p>';

                $html .= '<h2>Search Engine Snippet Preview</h2>
                <div class="seo-audit-snippet">';
                    if (isset($htmlInfo['titletext'])) {
    					$html .= '<div style="color: #1a0dab; font-size: 15px;">' . $htmlInfo['titletext'] . '</div>';
                    }
    				$html .= '<div style="color: #006621; font-size: 13px;">' . addScheme($htmlInfo['url'], 'http://') . '</div>';
    				if (isset($htmlInfo['description'])) {
    					$html .= '<div style="color: #545454; font-size: 13px;">' . $htmlInfo['description'] . '</div>';
    				}
                $html .= '</div>';

                $html .= '<h2>Most Common Keywords</h2>';
                if (!empty($htmlInfo['wordCountMax'])) {
					$html .= '<p>There is likely no optimal keyword density (search engine algorithms have evolved beyond keyword density metrics as a significant ranking factor). It can be useful, however, to note which keywords appear most often on your page and if they reflect the intended topic of your page. More importantly, the keywords on your page should appear within natural sounding and grammatically correct copy.</p>';

					foreach ($htmlInfo['wordCountMax'] as $wordMaxKey => $wordMaxValue) {
						$html .= '<div><code>' . $wordMaxKey . '</code> (' . $wordMaxValue . ' times)</div>';
					}
				} else {
					$html .= '<p class="is-ugly">Your page does not have any repeated keywords.</?><?php>';
				}

                $html .= '<h2>Headings</h2>';
                if (isset($htmlInfo['h1'])) {
					$html .= '<p class="is-good">Your page has these <code>H1</code> headings:</p>';

					foreach ($htmlInfo['h1'] as $h1) {
						$html .= '<div><b>' . $h1 . '</b></div>';
					}
				} else {
					$html .= '<div class="is-bad">Your page does not have H1 headings.</div>';
				}

                if (isset($htmlInfo['h2'])) {
                    $html .= '<p class="is-good">Your page has these <code>H2</code> headings:</p>';

					foreach ($htmlInfo['h2'] as $h2) {
						$html .= '<div><b>' . $h2 . '</b></div>';
					}
				} else {
					$html .= '<div class="is-bad">Your page does not have H2 headings.</div>';
				}

                $html .= '<h2>Search Engine Visibility</h2>';

                if ((int) $htmlInfo['robots'] === 200) {
					$html .= '<p class="is-good">Your site uses a <code>robots.txt</code> file (<a href="' . addScheme($htmlInfo['url'], 'http://') . '/robots.txt">' . addScheme($htmlInfo['url'], 'http://') . '/robots.txt</a>)</p>';
				} else {
					$html .= '<p class="is-ugly">Your site does not use a <code>robots.txt</code> file.</p>';
				}

                if ((int) $htmlInfo['sitemap'] === 200) {
					$html .= '<p class="is-good">Your site uses a <code>sitemap.xml</code> file (<a href="' . addScheme($htmlInfo['url'], 'http://') . '/sitemap.xml">' . addScheme($htmlInfo['url'], 'http://') . '/sitemap.xml</a>)</p>';
				} else {
					$html .= '<p class="is-bad">Your site does not use a <code>sitemap.xml</code> file.</p>';
				}

                if ((int) $htmlInfo['sitemap_index'] === 200) {
					$html .= '<p class="is-good">Your site uses a <code>sitemap_index.xml</code> file (<a href="' . addScheme($htmlInfo['url'], 'http://') . '/sitemap_index.xml">' . addScheme($htmlInfo['url'], 'http://') . '/sitemap_index.xml</a>)</p>';
				} else {
					$html .= '<p class="is-bad">Your site does not use a <code>sitemap_index.xml</code> file.</p>';
				}

                if (!empty($htmlInfo['brokenLinkCount']) && (int) $htmlInfo['brokenLinkCount'] !== 0) {
					$html .= '<p class="is-ugly">Your page has ' . $htmlInfo['brokenLinkCount'] . ' broken links.</p>';
				} else {
					$html .= '<p class="is-good">Your page does not have any broken links.</p>';
				}

                if (!empty($htmlInfo['images'])) {
					if (isset($htmlInfo['images']['totImgs']) && (int) $htmlInfo['images']['totImgs'] !== 0) {
						if ($htmlInfo['images']['diff'] <= 0) {
							$html .= '<p class="is-good">' . $htmlInfo['images']['totImgs'] . ' image(s) found on your page, all having <code>ALT</code> text.</p>';
						} else {
							$html .= '<p class="is-ugly">' . $htmlInfo['images']['totImgs'] . ' image(s) found in your page, ' . $htmlInfo['images']['diff'] . ' having no <code>ALT</code> text.</p>';
						}
					} else {
						$html .= '<p class="is-ugly">Your page does not have any images.</p>';
					}
				} else {
					$html .= '<p class="is-ugly">Your page does not have any images.</p>';
				}

                if (isset($htmlInfo['shortcut icon']) || isset($htmlInfo['icon'])) {
					$html .= '<p class="is-good">Your site has a favicon.</p>';
				} else {
					$html .= '<p class="is-bad">Your site does not have a favicon.</p>';
				}

                $html .= '<h2>Tracking &amp; Measurement</h2>';

                if ((bool) $htmlInfo['googleAnalytics'] === true) {
					$html .= '<p class="is-good">Your site is tracked via Google Analytics.</p>';
				} else {
					$html .= '<p class="is-ugly">Your site is not tracked via Google Analytics.</p>';
				}

				if ((bool) $htmlInfo['googleTagManager'] === true) {
					$html .= '<p class="is-good">Your site is tracked via Google Tag Manager.</p>';
				} else {
					$html .= '<p class="is-ugly">Your site is not tracked via Google Tag Manager.</p>';
				}

                $html .= '<h2>Assets &amp; Resources</h2>';

                if (!empty($htmlInfo['css'])) {
					if ((int) $htmlInfo['css']['cssCount'] > 0) {
						$html .= '<p>Your page has ' . $htmlInfo['css']['cssCount'] . ' external CSS files.</p>';

						if ((int) $htmlInfo['css']['cssMinCount'] > 0) {
							$html .= '<div>' . $htmlInfo['css']['cssMinCount'] . ' CSS files are minified.</div>';
						} else {
							$html .= '<div>No CSS files are minified.</div>';
						}

						if (!empty($htmlInfo['css']['cssNotMinFiles'])) {
							$html .= '<div>These CSS files are not minified:</div>';

							foreach ($htmlInfo['css']['cssNotMinFiles'] as $cNMF) {
								$html .= '<div><code class="small">' . $cNMF . '</code></div>';
							}
						}
					} else {
						$html .= '<p>No external CSS files found.</p>';
					}
				} else {
					$html .= '<p>No external CSS files found.</p>';
				}

                if (!empty($htmlInfo['js'])) {
					if ((int) $htmlInfo['js']['jsCount'] > 0) {
						$html .= '<p>Your page has ' . $htmlInfo['js']['jsCount'] . ' external JS files.</p>';

						if ((int) $htmlInfo['js']['jsMinCount'] > 0) {
							$html .= '<div>' . $htmlInfo['js']['jsMinCount'] . ' JS files are minified.</div>';
						} else {
							$html .= '<div>No JS files are minified.</div>';
						}

						if (!empty($htmlInfo['js']['jsNotMinFiles'])) {
							$html .= '<div>These JS files are not minified:</div>';

							foreach ($htmlInfo['js']['jsNotMinFiles'] as $jNMF) {
								$html .= '<div><code class="small">' . $jNMF . '</code></div>';
							}
						}
					} else {
						$html .= '<p>No external JS files found.</p>';
					}
				} else {
					$html .= '<p>No external JS files found.</p>';
				}
			} else {
				$html .= '<h2 class="is-bad">Your site could not be loaded.</h2>';
			}
		$html .= '</div>';

		return $html;
	}


$url = $_GET['url'];
echo getSeoReport($url);
