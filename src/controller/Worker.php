<?php


namespace src\controller;


use JtM\Timer\Timer;
use Phpml\Classification\MLPClassifier;
use Phpml\NeuralNetwork\ActivationFunction\PReLU;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Worker extends Command
{
    protected static $defaultName = 'src:run';

    /**
     * @var OutputInterface
     */
    private $output;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $finder = new Finder();
        $finder->in(__DIR__ . '/../res');
        $imgColors = [];
        foreach ($finder as $fileInfo) {
            $this->getColor($fileInfo, $imgColors);
        }
        $this->learnNN($imgColors);
        return 0;
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param array $imgColors
     */
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

    private function learnNN(array $config)
    {
        $nnFile = __DIR__ . '/../data/nn.bin';
        if (file_exists($nnFile)) {
            $this->output->write('load ...');
            $nn = unserialize(base64_decode(file_get_contents($nnFile)));
            $this->output->writeln(' done');
        } else {
            @touch($nnFile);
            $this->output->write('create ...');
            $nn = new MLPClassifier(1024, [[16, new PReLU()], [16, new PReLU()]], ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']);
            $this->output->writeln(' done');
        }
        $all = [];
        foreach ($config as $name => $item) {
//            $this->output->write($name . ' [' . $item['number'] . '] : ...');
//            $timer = new Timer();
//            $timer->start();
//            $nn->train([$item['colors']], [$item['number']]);
            $all['train'][] = $item['colors'];
            $all['res'][] = $item['number'];
//            $timer->stop();
//            $this->output->writeln(' done (' . $timer->diffTime(). ' s)');
//            $this->output->write('save ...');
//            @unlink($nnFile);
//            file_put_contents($nnFile, base64_encode(serialize($nn)));
//            $this->output->writeln(' done');
        }

        $this->output->write('train all: ...');
        $timer = new Timer();
        $timer->start();
        $nn->train($all['train'], $all['res']);
        $timer->stop();
        $this->output->writeln(' done (' . $timer->diffTime(). ' s)');

        $this->output->write('save ...');
        unlink($nnFile);
        file_put_contents($nnFile, base64_encode(serialize($nn)));
        $this->output->writeln(' done');
    }
}