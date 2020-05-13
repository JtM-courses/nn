<?php


namespace src\controller;


use JtM\Timer\Timer;
use Phpml\Classification\MLPClassifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class TestData extends Command
{
    protected static $defaultName = 'src:test';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nnFile = __DIR__ . '/../data/nn.bin';
        if (file_exists($nnFile)) {
            $output->write('load ...');
            /** @var MLPClassifier $nn */
            $nn = unserialize(base64_decode(file_get_contents($nnFile)));
            $output->writeln(' done');
        } else {
            throw new \Exception('not data');
        }

        $finder = new Finder();
        $finder->in(__DIR__ . '/../res/')->name('0.30.a.n.png');
        foreach ($finder as $fileInfo) {
            $imgColors = [];
            $this->getColor($fileInfo, $imgColors);
            $name = $fileInfo->getFilenameWithoutExtension();
//            dump($imgColors); die;
            $timer = new Timer();
            $timer->start();
            $res = $nn->predict($imgColors[$name]['colors']);
            $timer->stop();

            dump([
                'file' => $name,
                'result' => $res,
                'time' => $timer->diffTime()
            ]);
        }
        return 0;
    }

    private function getColor(SplFileInfo $fileInfo, array &$imgColors)
    {
        $img = imagecreatefrompng($fileInfo->getPathname());
        $width = imagesx($img);
        $height = imagesy($img);
        $name = $fileInfo->getFilenameWithoutExtension();
        for($x = 0; $x < $width; $x++) {
            for($y = 0; $y < $height; $y++) {
                // pixel color at (x, y)
                $color = imagecolorat($img, $x, $y);
//                    $rgb = imagecolorsforindex($img, $color);
                $imgColors[$name]['colors'][] = round($color / 10);
            }
        }
        $imgColors[$name]['number'] = explode('.', $name)[0];
    }
}