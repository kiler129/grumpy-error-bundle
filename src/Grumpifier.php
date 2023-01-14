<?php

namespace NoFlash\GrumpyError;

class Grumpifier
{
    /**
     * @var list<array{string, int}> List of texts appearing at random with corresponding font-size. No amount of
     *                               calculations nor CSS wizardy lead to good results, so I gave up and added manual
     *                               font size.
     */
    private $speechTexts = [
        ['You call this code?', 20],
        ['This is your fault', 20],
        ['Pretend like it\'s Friday', 20],
        ['Your code is like Monday', 17],
        ['You made it a<br/>Cacophony', 16],
        ['Your struggling made my day', 16],
        ['Stop looking at me', 19],
        ['Go back to work', 19],
        ['Why are they keeping you again?', 16],
        ['Error? Good.', 22],
        ['This is my happy face', 18],
        ['How about... no.', 20],
        ['You deserve it', 20],
        ['What\'s the test coverage?', 17],
        ['Lorem ipsum bla blah blah Lorem ipsum bla blah blah Lorem ipsum bla blah blah ', 15],
    ];

    private $bannerTexts = [
        'NO PETTING!',
        'GO AWAY!',
        'STOP THIS!',
        'I SAID NO!',
    ];

    /**
     * Filters the original output HTML to add some grumpiness ;)
     *
     * @param string $result
     *
     * @return string
     */
    public function filterString($result)
    {
        $req = \preg_match(
            '/class=.*?exception\-illustration.*?>(.*?<svg.*?\/svg.*?)<\/div>/s',
            $result,
            $out,
            \PREG_OFFSET_CAPTURE
        );
        $capLeng = \strlen($out[1][0]);
        $before = \substr($result, 0, $out[1][1]);
        $after = \substr($result, $out[1][1] + $capLeng);

        $bubbleSpec = $this->speechTexts[\mt_rand(0, \count($this->speechTexts)-1)];
        $bannerText = $this->bannerTexts[\mt_rand(0, \count($this->bannerTexts)-1)];

        return \sprintf('
            %s
            <style>%s</style>
            %s
            <blockquote class="gcr" style="font-size: %spx"><span class="gcrs"></span><span class="gcst">%s</span></blockquote>
            <img class="gcs" src="data:image/png;base64,%s" alt="" />
            <div class="gcnp gcs">%s</div>
            %s',
            $before,
            \file_get_contents(__DIR__ . '/../resources/error.css'),
            \file_get_contents(__DIR__ . '/../resources/bubble.svg'),
            $bubbleSpec[1], $bubbleSpec[0],
            \base64_encode(\file_get_contents(__DIR__ . '/../resources/grumpy.png')),
            $bannerText,
            $after
        );
    }
}
