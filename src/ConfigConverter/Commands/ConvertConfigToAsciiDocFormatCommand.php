<?php
/**
 * The MIT License (MIT)
 *
 * @author Matthew Setter <matthew@matthewsetter.com>
 * @copyright Copyright (c) 2018, ownCloud GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace ConfigConverter\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Filter\PregReplace;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

/**
 * Class ConvertConfigToAsciiDocFormatCommand
 * It extracts the code comments out of ownCloud's config/config.sample.php and creates an AsciiDoc equivalent.
 *
 * @package ConfigConverter\Commands
 */
class ConvertConfigToAsciiDocFormatCommand extends Command
{
    /**
     * Array is defined with []
     */
    const FILTER_REGEX_SHORT_ARRAY_SYNTAX = '/(\$CONFIG = \[)([\S\s|\n|\r]*)(\];)/';

    /**
     * Array is defined array()
     */
    const FILTER_REGEX_TRADITIONAL_ARRAY_SYNTAX = '/(\$CONFIG = array\()([\S\s|\n|\r]*)(\);)/';

    /**
     * ConvertConfigToAsciiDocFormatCommand constructor.
     *
     * @todo Supply inputFilter and renderer via constructor-injection
     */
    const OUTPUT_MISSING_INPUT_OR_OUTPUT_FILE = 'Please run --help for arguments and options';

    /**
     * Header separation text.
     */
	const HEADER_END_TEXT = '// header end do not delete or edit this line';

    /**
     * Header separation regex rule. Will catch all text above the defined text.
     */
	const FILTER_REGEX_HEADER = '/([\S\s|\n|\r]*)(?=(\/\/ header end do not delete or edit this line))/';

    /**
     * @var \phpDocumentor\Reflection\DocBlockFactory
     */
    private $phpDoc;

    /**
     * @var TemplateRendererInterface
     */
    private $renderer;

    /**
     * @var InputFilter
     */
    private $inputFilter;

    /**
     * ConvertConfigToAsciiDocFormatCommand constructor.
     *
     * @todo Supply inputFilter and renderer via constructor-injection
     */
    public function __construct()
    {
        parent::__construct();

        $this->inputFilter = $this->buildInputFilter();

        $this->renderer = new TwigRenderer(
            new \Twig_Environment(
                new \Twig_Loader_Filesystem(__DIR__ . '/../../templates'),
                []
            )
        );

        $this->phpDoc = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
    }

    /**
     * The core of the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!empty($input->getOption('input-file')) and !empty($input->getOption('output-file'))) {

			if (!is_readable($input->getOption('input-file'))) {
				$output->writeln("\n inputFile cant be read, check path and name. Exiting \n");
				exit;
			}
			if (is_writable($input->getOption('output-file'))) {
				$output->writeln("\n Converting... \n");
				$this->convertFile(
					$input->getOption('input-file'),
					$input->getOption('output-file'),
					$input->getOption('tag')
				);
			} else {
				$output->writeln("\n outputFile cant be written, check permissions. Exiting \n");
				exit;
			}
        } else {
            $output->writeln(self::OUTPUT_MISSING_INPUT_OR_OUTPUT_FILE);
        }
    }

    /**
     * Configures the command
     *
     * @todo Need a default value for tag
     * @todo Create the output file if it doesn't already exist
     */
    protected function configure()
    {
        $this
            ->setName('config:convert-adoc')
            ->setDescription('Converts config.(apps).sample.php to config_(apps)_sample_php_parameters.adoc')
            ->setDefinition([
                new InputOption(
                    'input-file',
                    'i',
                    InputOption::VALUE_REQUIRED,
                    'The location of config.sample.php or config.apps.sample.php'
                ),
                new InputOption(
                    'output-file',
                    'o',
                    InputOption::VALUE_REQUIRED,
                    'The location of config_sample_php_parameters.adoc or config_aps_sample_php_parameters.adoc'
                ),
                new InputOption(
                    'tag',
                    't',
                    InputOption::VALUE_OPTIONAL,
                    'Tag to use for copying a config entry (default: see)'
                ),
            ])
            ->setHelp('Converts config.sample.php to config_sample_php_parameters.adoc');
    }

    /**
     * @param string $content
     * @return array
     */
    protected function extractCoreContent(string $content) : array
    {
        # match for $CONFIG = array(
        preg_match_all(
            self::FILTER_REGEX_TRADITIONAL_ARRAY_SYNTAX,
            $content,
            $matches,
            PREG_PATTERN_ORDER,
            0
        );

        # match for $CONFIG = [
        if ($matches[1] == []) {
            preg_match_all(
                self::FILTER_REGEX_SHORT_ARRAY_SYNTAX,
                $content,
                $matches,
                PREG_PATTERN_ORDER,
                0
            );
        }

        # nothing found, print error and exit
        if ($matches[1] == []) {
           echo ' No $CONFIG = [] or $CONFIG = array() found in source file. Exiting.' . "\n\n";
        }

        return explode('/**', $matches[2][0]);
    }

    /**
     * @param string $content
     * @return array
     */
    protected function extractHeaderContent(string $content) : string
    {
        preg_match(
            self::FILTER_REGEX_HEADER,
            $content,
            $matches
        );
        return $matches[0];
    }

    /**
     * Convert the sample config file to an AsciiDoc (.adoc) documentation equivalent
     *
     * @param string $inputFile
     * @param string $outputFile
     * returns string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function convertFile(string $inputFile, string $outputFile)
    {

        $templateData = [];
        $contents = (string) file_get_contents($inputFile);
        $blocks = $this->extractCoreContent($contents);

		# get the header text of the target file - if available
		if (file_exists($outputFile)) {
			$contents = trim((string) file_get_contents($outputFile));
		} else {
			$contents = "";
		}

		# create the header based if there is already a header content set
		if (empty($contents)) {
			# if the file has no content, set the header manually
			$header = self::HEADER_END_TEXT . "\n\n";
		} else {
			$header = $this->extractHeaderContent($contents) . self::HEADER_END_TEXT . "\n\n";
		}

        foreach ($blocks as $block) {
            $templateData = $this->parseDocBlock($block, $templateData, $header);
        }
        $this->writeOutputFile($outputFile, $templateData, $header);
    }

    /**
     * @param string $docBlock
     * @param string $codeBlock
     * @return array
     */
    public function buildRowItem(string $docBlock, string $codeBlock = null) : array
    {
        $this->inputFilter->setData([
            'summary' => $this->phpDoc->create($docBlock)->getSummary(),
            'description' => $this->phpDoc->create($docBlock)->getDescription()->render(),
            'code' => $codeBlock,
        ]);

        $item = [
            'summary' => $this->inputFilter->getValue('summary'),
            'description' => $this->inputFilter->getValue('description'),
            'section_header' => ($codeBlock === null ? true : false),
            'code' => $this->inputFilter->getValue('code'),
        ];

        return $item;
    }

    /**
     * @param string $outputFile
     * @param array $templateData
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function writeOutputFile(string $outputFile, array $templateData, string $header)
    {
        file_put_contents(
            $outputFile,
            $header . $this->renderer->render('configtoasciidocformat.html.twig', ['blocks' => $templateData])
        );
    }

    /**
     * @param string $block
     * @param array $templateData
     * @return array
     */
    public function parseDocBlock(string $block, array $templateData) : array
    {
        if (trim($block, " \t\n\r\0\x0B") !== '') {
            $block = '/**' . $block;
            $codeBlock = null;
            $parts = explode(' */', $block);

            if (count($parts) == 2 && !empty(trim($parts[1]))) {
                list($docBlock, $codeBlock) = $parts;
            } else {
                list($docBlock) = $parts;
            }

            $docBlock .= " */";
            $templateData[] = $this->buildRowItem($docBlock, $codeBlock);
        }

        return $templateData;
    }

    /**
     * @return InputFilter
     */
    protected function buildInputFilter() : InputFilter
    {
        $descriptionInput = (new Input('description'));
        $descriptionInput->getFilterChain()
            ->attachByName('stringtrim')
            ->attach(new PregReplace([
                'pattern' => [
                    '/^([^\n][\s]*)(?=-)/m',
                    '/(.. warning::[\n\s]*)/m',
                    '/``/m',
                    '/\t/m'
                ],
                'replacement' => [
                    '',
                    'WARNING: ',
                    '`',
                    ' '
                ]
            ]));

        $summaryInput = (new Input('summary'));
        $summaryInput->getFilterChain()
            ->attachByName('stringtrim')
            ->attach(new PregReplace([
                'pattern' => '/^[\s]*\*[\s\n]*/m',
                'replacement' => ''
            ]));

        $codeInput = (new Input('code'));
        $codeInput->getFilterChain()
            ->attachByName('stringtrim')
            ->attach(new PregReplace([
                'pattern' => '/^[ ]{4}/m',
                'replacement' => ''
            ]));

        $inputFilter = new InputFilter();
        $inputFilter
            ->add($descriptionInput)
            ->add($summaryInput)
            ->add($codeInput);

        return $inputFilter;
    }

}
