<?php declare(strict_types=1);

namespace Intellow\MakeRouteForLaravel\CaseConverter;

use Intellow\MakeRouteForLaravel\CaseConverter\Glue\AdaCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\CamelCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\CobolCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\DashGluer;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\DotNotation;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\Gluer;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\KebabCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\LowerCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\MacroCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\PascalCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\SentenceCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\SnakeCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\SpaceGluer;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\TitleCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\TrainCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\UnderscoreGluer;
use Intellow\MakeRouteForLaravel\CaseConverter\Glue\UpperCase;
use Intellow\MakeRouteForLaravel\CaseConverter\Split\DashSplitter;
use Intellow\MakeRouteForLaravel\CaseConverter\Split\DotSplitter;
use Intellow\MakeRouteForLaravel\CaseConverter\Split\SpaceSplitter;
use Intellow\MakeRouteForLaravel\CaseConverter\Split\Splitter;
use Intellow\MakeRouteForLaravel\CaseConverter\Split\UnderscoreSplitter;
use Intellow\MakeRouteForLaravel\CaseConverter\Split\UppercaseSplitter;
use function is_subclass_of;
use function mb_strpos;
use function preg_match;

/**
 * Convert string between different naming conventions.
 *
 * Handled formats:
 *
 * - Ada case
 * - Camel case
 * - Cobol case
 * - Kebab case
 * - Lower case
 * - Macro case
 * - Pascal case
 * - Sentence case
 * - Snake case
 * - Title case
 * - Train case
 * - Upper case
 *
 * @method self fromAda() Treat input string as _Ada case_
 * @method self fromCamel() Treat input string as _Camel case_
 * @method self fromCobol() Treat input string as _Cobol case_
 * @method self fromDot() Treat input string as _Dot notation_
 * @method self fromKebab() Treat input string as _Kebab case_
 * @method self fromLower() Treat input string as _Lower case_
 * @method self fromMacro() Treat input string as _Macro case_
 * @method self fromPascal() Treat input string as _Pascal case_
 * @method self fromSentence() Treat input string as _Sentence case_
 * @method self fromSnake() Treat input string as _Snake case_
 * @method self fromTitle() Treat input string as _Title case_
 * @method self fromTrain() Treat input string as _Train case_
 * @method self fromUpper() Treat input string as _Upper case_
 *
 * @method string toAda() Return string in _Ada case_ format
 * @method string toCamel() Return string in _Camel case_ format
 * @method string toCobol() Return string in _Cobol case_ format
 * @method string toDot() Return string in _Dot notation_
 * @method string toKebab() Return string in _Kebab case_ format
 * @method string toLower() Return string in _Lower case_ format
 * @method string toMacro() Return string in _Macro case_ format
 * @method string toPascal() Return string in _Pascal case_ format
 * @method string toSentence() Return string in _Sentence case_ format
 * @method string toSnake() Return string in _Snake case_ format
 * @method string toTitle() Return string in _Title case_ format
 * @method string toTrain() Return string in _Train case_ format
 * @method string toUpper() Return string in _Upper case_ format
 *
 * @see     https://softwareengineering.stackexchange.com/questions/322413/bothered-by-an-unknown-letter-case-name
 * @see     http://www.unicode.org/charts/case/
 * @package Intellow\MakeRouteForLaravel\CaseConverter
 * @author  Jawira Portugal <dev@tugal.be>
 */
class Convert
{
    /**
     * @var string Input string to convert
     */
    protected $source;

    /**
     * @var string[] Words extracted from input string
     */
    protected $words;

    /**
     * @var bool
     */
    protected $forceSimpleCaseMapping;

    /**
     * Constructor method
     *
     * @param string $source String to convert
     *
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    public function __construct(string $source)
    {
        $this->source                 = $source;
        $this->forceSimpleCaseMapping = false;
        $this->fromAuto();
    }

    /**
     * Auto-detect naming convention
     *
     * @return \Intellow\MakeRouteForLaravel\CaseConverter\Convert
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    public function fromAuto(): self
    {
        $splitter = $this->analyse($this->source);
        $this->extractWords($splitter);

        return $this;
    }

    /**
     * Detects word separator of $input string and tells you what strategy you should use.
     *
     * @param string $input String to be analysed
     *
     * @return \Intellow\MakeRouteForLaravel\CaseConverter\Split\Splitter
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    protected function analyse(string $input): Splitter
    {
        if (mb_strpos($input, UnderscoreGluer::DELIMITER)) {
            $splittingStrategy = new UnderscoreSplitter($input);
        } elseif (mb_strpos($input, DashGluer::DELIMITER)) {
            $splittingStrategy = new DashSplitter($input);
        } elseif (mb_strpos($input, SpaceGluer::DELIMITER)) {
            $splittingStrategy = new SpaceSplitter($input);
        } elseif (mb_strpos($input, DotNotation::DELIMITER)) {
            $splittingStrategy = new DotSplitter($input);
        } elseif ($this->isUppercaseWord($input)) {
            $splittingStrategy = new UnderscoreSplitter($input);
        } else {
            $splittingStrategy = new UppercaseSplitter($input);
        }

        return $splittingStrategy;
    }

    /**
     * Returns true if $input string is a single word composed only by uppercase characters.
     *
     * ```
     * isUppercaseWord('BRUSSELS'); // true
     * isUppercaseWord('Brussels'); // false
     * ```
     *
     * @see     https://www.regular-expressions.info/unicode.html#category
     *
     * @param string $input String to be tested.
     *
     * @return bool
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    protected function isUppercaseWord(string $input): bool
    {
        $match = preg_match('#^\p{Lu}+$#u', $input);

        if (false === $match) {
            throw new CaseConverterException('Error executing regex'); // @codeCoverageIgnore
        }

        return 1 === $match;
    }

    /**
     * Main function, receives input string and then it stores extracted words into an array.
     *
     * @param \Intellow\MakeRouteForLaravel\CaseConverter\Split\Splitter $splitter
     *
     * @return $this
     */
    protected function extractWords(Splitter $splitter): self
    {
        $this->words = $splitter->split();

        return $this;
    }

    /**
     * Returns original input string
     *
     * @return string Original input string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Handle `to*` methods and `from*` methods
     *
     * @param string $methodName
     * @param array  $arguments
     *
     * @return string|\Intellow\MakeRouteForLaravel\CaseConverter\Convert
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    public function __call($methodName, $arguments)
    {
        if (0 === mb_strpos($methodName, 'from')) {
            $result = $this->handleSplitterMethod($methodName);
        } elseif (0 === mb_strpos($methodName, 'to')) {
            $result = $this->handleGluerMethod($methodName);
        } else {
            throw new CaseConverterException("Unknown method: $methodName");
        }

        return $result;
    }

    /**
     * Methods to explicitly define naming conventions for input string
     *
     * @param string $methodName
     *
     * @return $this
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    protected function handleSplitterMethod(string $methodName): self
    {
        switch ($methodName) {
            case 'fromCamel':
            case 'fromPascal':
                $splitterName = UppercaseSplitter::class;
                break;
            case 'fromSnake':
            case 'fromAda':
            case 'fromMacro':
                $splitterName = UnderscoreSplitter::class;
                break;
            case 'fromKebab':
            case 'fromTrain':
            case 'fromCobol':
                $splitterName = DashSplitter::class;
                break;
            case 'fromLower':
            case 'fromUpper':
            case 'fromTitle':
            case 'fromSentence':
                $splitterName = SpaceSplitter::class;
                break;
            case 'fromDot':
                $splitterName = DotSplitter::class;
                break;
            default:
                throw new CaseConverterException("Unknown method: $methodName");
                break;
        }

        $splitter = $this->createSplitter($splitterName, $this->source);
        $this->extractWords($splitter);

        return $this;
    }

    /**
     * @param string $className Class name in string format
     * @param string $source    Input string to be split
     *
     * @return \Intellow\MakeRouteForLaravel\CaseConverter\Split\Splitter
     */
    protected function createSplitter(string $className, string $source): Splitter
    {
        assert(is_subclass_of($className, Splitter::class));

        return new $className($source);
    }

    /**
     * Handles all methods starting by `to*`
     *
     * @param string $methodName
     *
     * @return string
     * @throws \Intellow\MakeRouteForLaravel\CaseConverter\CaseConverterException
     */
    protected function handleGluerMethod(string $methodName): string
    {
        switch ($methodName) {
            case 'toAda':
                $className = AdaCase::class;
                break;
            case 'toCamel':
                $className = CamelCase::class;
                break;
            case 'toCobol':
                $className = CobolCase::class;
                break;
            case 'toKebab':
                $className = KebabCase::class;
                break;
            case 'toLower':
                $className = LowerCase::class;
                break;
            case 'toMacro':
                $className = MacroCase::class;
                break;
            case 'toPascal':
                $className = PascalCase::class;
                break;
            case 'toSentence':
                $className = SentenceCase::class;
                break;
            case 'toSnake':
                $className = SnakeCase::class;
                break;
            case 'toTitle':
                $className = TitleCase::class;
                break;
            case 'toTrain':
                $className = TrainCase::class;
                break;
            case 'toUpper':
                $className = UpperCase::class;
                break;
            case 'toDot':
                $className = DotNotation::class;
                break;
            default:
                throw new CaseConverterException("Unknown method: $methodName");
                break;
        }

        $gluer = $this->createGluer($className, $this->words, $this->forceSimpleCaseMapping);

        return $gluer->glue();
    }

    /**
     * @param string $className              Class name in string format
     * @param array  $words                  Words to glue
     * @param bool   $forceSimpleCaseMapping Should _Simple Case-Mapping_ be forced?
     *
     * @return \Intellow\MakeRouteForLaravel\CaseConverter\Glue\Gluer
     */
    protected function createGluer(string $className, array $words, bool $forceSimpleCaseMapping): Gluer
    {
        assert(is_subclass_of($className, Gluer::class));

        return new $className($words, $forceSimpleCaseMapping);
    }

    /**
     * Detected words extracted from original string.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->words;
    }

    /**
     * Forces to use Simple Case-Mapping
     *
     * Call this method if you want to maintain the behaviour before PHP 7.3
     *
     * @see https://unicode.org/faq/casemap_charprop.html
     * @return \Intellow\MakeRouteForLaravel\CaseConverter\Convert
     */
    public function forceSimpleCaseMapping(): self
    {
        $this->forceSimpleCaseMapping = true;

        return $this;
    }
}
