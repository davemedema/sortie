<?php
namespace Sortie;

use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Str;

class Sortie
{
  /**
   * The processed expressions.
   *
   * @var array
   */
  protected $expressions = [];

  /**
   * The raw field.
   *
   * @var string
   */
  protected $field = '';

  /**
   * The processed properties.
   *
   * @var array
   */
  protected $properties = [];

  /**
   * Create a new Sortie instance.
   *
   * @param string $field
   */
  public function __construct($field)
  {
    $this->setField($field);
  }

  // Public Methods
  // ---------------------------------------------------------------------------

  /**
   * getExpressions
   *
   * @return array
   */
  public function getExpressions()
  {
    return $this->expressions;
  }

  /**
   * getField
   *
   * @return string
   */
  public function getField()
  {
    return $this->field;
  }

  /**
   * getProperties
   *
   * @return array
   */
  public function getProperties()
  {
    return $this->properties;
  }

  /**
   * process
   *
   * @param array $data
   * @param bool  $sanitized
   *
   * @return string
   */
  public function process(array $data, bool $sanitize = false): string
  {
    if ($sanitize) {
      $data = static::sanitizeData($data);
    }

    $processed = $this->field;

    foreach ($this->expressions as $expression) {
      $replace = '';

      switch ($expression['type']) {
      case 'boolean':
        $replace = $this->replaceBoolean($expression, $data);
        break;
      case 'calc':
        $replace = $this->replaceCalc($expression, $data);
        break;
      case 'simple':
        $replace = $this->replaceSimple($expression, $data);
        break;
      }

      $search    = sprintf('[%s]', $expression['expression']);
      $processed = str_replace($search, $replace, $processed);
    }

    $processed = $this->modifyClean($processed);
    $processed = str_replace('\[', '[', $processed);
    $processed = str_replace('\]', ']', $processed);

    return $processed;
  }

  /**
   * setField
   *
   * @param string $field
   *
   * @return void
   */
  public function setField($field)
  {
    $this->field = static::sanitizeField($field);

    $this->hydrate();
  }

  // Protected Methods
  // ---------------------------------------------------------------------------

  /**
   * Processes and returns the options in an expression and populates
   * `$this->properties` along the way.
   *
   * @param string $expression
   *
   * @return array
   */
  protected function getOptions($expression)
  {
    $options    = [];
    $rawOptions = explode('|', $expression);

    foreach ($rawOptions as $rawOption) {
      $optionParts = explode('->', $rawOption);
      $property    = array_shift($optionParts);

      $options[] = [
        'property'  => $property,
        'modifiers' => $optionParts
      ];

      $this->properties[] = $property;
    }

    return $options;
  }

  /**
   * hydrate
   *
   * @return void
   */
  protected function hydrate()
  {
    // Reset the hydrated expressions and properties first in case the field is
    // empty or invalid.
    $this->expressions = [];
    $this->properties  = [];

    if (!$this->field) {
      return;
    }

    preg_match_all('/(?<!\\\\)\\[([^\\]]+)(?<!\\\\)\\]/u', $this->field, $matches);

    if (empty($matches[1])) {
      return;
    }

    foreach ($matches[1] as $expression) {
      $expression = static::sanitizeExpression($expression);

      if (preg_match('/^if\s*\(([^\)]+)\)\s*{([^}]*)}\s*else\s*{([^}]*)}$/iu', $expression, $matches)) {
        $this->expressions[] = [
          'expression' => $expression,
          'parts'      => array_slice($matches, 1),
          'type'       => 'boolean',
        ];
      } else if (preg_match('/^(add|div|mul|sub)\(([^\)]+)\)$/iu', $expression, $matches)) {
        $this->expressions[] = [
          'expression' => $expression,
          'parts'      => array_slice($matches, 1),
          'type'       => 'calc',
        ];
      } else {
        $this->expressions[] = [
          'expression' => $expression,
          'options'    => $this->getOptions($expression),
          'type'       => 'simple',
        ];
      }
    }
  }

  /**
   * Modifies an input.
   *
   * @param string $input
   * @param string $modifier
   *
   * @throws Exception if the modifier is not whitelisted.
   *
   * @return string
   */
  protected function modify(string $input, string $modifier): string
  {
    // Since we can now fallback to static strings, we no longer have a use
    // case for changing an empty string to something more opinionated.
    if ($input === '') {
      return '';
    }

    try {
      $parts = explode(':', $modifier);

      // TODO: Find an efficient way to put some logging in to determine which
      // modifiers are used the most. We can then adjust the switch ordering,
      // or better yet, implement a more modern dynamic method approach.
      switch ($parts[0]) {
      case 'bodystyle':
        return $this->modifyBodyStyle($input);
      case 'camel':
        return $this->modifyCamel($input);
      case 'clean':
        return $this->modifyClean($input);
      case 'date':
        return $this->modifyDate($input, array_slice($parts, 1));
      case 'decimaltotime':
        return $this->modifyDecimalToTime($input);
      case 'drivetrain':
        return $this->modifyDrivetrain($input);
      case 'email':
        return $this->modifyEmail($input);
      case 'exception':
        return $this->modifyException($input);
      case 'fueltype':
        return $this->modifyFuelType($input);
      case 'kebab':
        return $this->modifyKebab($input);
      case 'limit':
        return $this->modifyLimit($input, array_slice($parts, 1));
      case 'lower':
        return $this->modifyLower($input, array_slice($parts, 1));
      case 'match':
        return $this->modifyMatch($input, array_slice($parts, 1));
      case 'number':
        return $this->modifyNumber($input, array_slice($parts, 1));
      case 'phone':
        return $this->modifyPhone($input, array_slice($parts, 1));
      case 'pick':
        return $this->modifyPick($input, array_slice($parts, 1));
      case 'piped':
        return $this->modifyPiped($input, array_slice($parts, 1));
      case 'plural':
        return $this->modifyPlural($input);
      case 'postal':
        return $this->modifyPostal($input, array_slice($parts, 1));
      case 'price':
        return $this->modifyPrice($input);
      case 'replace':
        return $this->modifyReplace($input, array_slice($parts, 1));
      case 'singular':
        return $this->modifySingular($input);
      case 'slug':
        return $this->modifySlug($input);
      case 'snake':
        return $this->modifySnake($input);
      case 'studly':
        return $this->modifyStudly($input);
      case 'substr':
        return $this->modifySubstr($input, array_slice($parts, 1));
      case 'timetodecimal':
        return $this->modifyTimeToDecimal($input);
      case 'title':
        return $this->modifyTitle($input, array_slice($parts, 1));
      case 'transmission':
        return $this->modifyTransmission($input);
      case 'trim':
        return $this->modifyTrim($input);
      case 'ucfirst':
        return $this->modifyUcfirst($input);
      case 'upper':
        return $this->modifyUpper($input, array_slice($parts, 1));
      case 'url':
        return $this->modifyUrl($input);
      case 'urldecode':
        return $this->modifyUrldecode($input);
      case 'words':
        return $this->modifyWords($input, array_slice($parts, 1));
      case 'year':
        return $this->modifyYear($input, array_slice($parts, 1));
      default:
        throw new Exception(sprintf('%s() expects modifier to be whitelisted.',
          __METHOD__
        ));
      }
    } catch (Exception $e) {
      // TODO: Find an efficient way to log these.
      return '';
    }
  }

  /**
   * modifyBodyStyle
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyBodyStyle(string $input): string
  {
    $input = strtoupper($input);

    switch ($input) {
    case 'CONVERTIBLE':
    case 'COUPE':
    case 'CROSSOVER':
    case 'HATCHBACK':
    case 'MINIVAN':
    case 'SEDAN':
    case 'SUV':
    case 'VAN':
    case 'WAGON':
      return $input;
    case 'PICKUP':
      return 'TRUCK';
    default:
      return 'OTHER';
    }
  }

  /**
   * modifyCamel
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyCamel(string $input): string
  {
    return Str::camel($input);
  }

  /**
   * modifyClean
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyClean(string $input): string
  {
    return trim(preg_replace('/\s+/', ' ', $input));
  }

  /**
   * modifyDate
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyDate(string $input, array $params): string
  {
    $carbon = Carbon::parse($input);

    if (count($params) < 1) {
      return $carbon->format('m/d/Y');
    }

    $format = implode(':', $params);
    $format = trim($format, '"');
    $format = trim($format, "'");

    switch ($format) {
    case 'ATOM':
      $format = DateTime::ATOM;
      break;
    case 'ISO8601':
      $format = DateTime::ATOM;
      break;
    case 'RFC3339':
      $format = DateTime::RFC3339;
      break;
    case 'datetime':
      $format = 'Y-m-d H:i:s';
      break;
    }

    return $carbon->format($format);
  }

  /**
   * modifyDecimalToTime
   *
   * As a general rule, we need to standardize what is returned from a modifier
   * when it is invalid. Here we return the origin input, but maybe we should
   * return and empty result instead.
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyDecimalToTime(string $input): string
  {
    if (!preg_match('/^-?(\d*)?(\.)?(\d*)$/um', $input)) {
      return $input;
    }

    $float = (float)$input;

    if ($float === 0.0) {
      return '0:00';
    }

    $sign = $float < 0 ? '-' : '';

    $absolute = abs($float);

    $hours = floor($absolute);
    $fraction = $absolute - $hours;

    if ($fraction <= 0 || $fraction >= 1) {
      return sprintf('%s%d:00', $sign, $hours);
    }

    $minutes = round(60 * $fraction);

    if ($minutes <= 0) {
      return sprintf('%s%d:00', $sign, $hours);
    }

    $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);

    return sprintf('%s%d:%s', $sign, $hours, $minutes);
  }

  /**
   * modifyDrivetrain
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyDrivetrain(string $input): string
  {
    $input = strtoupper($input);

    switch ($input) {
    case 'AWD':
    case 'FWD':
    case 'RWD':
      return $input;
    case '4WD':
      return '4X4';
    default:
      return 'Other';
    }
  }

  /**
   * modifyEmail
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyEmail(string $input): string
  {
    $emails = explode(',', $input);

    $email = trim($emails[0]);

    return filter_var($email, FILTER_VALIDATE_EMAIL)
      ? strtolower($email)
      : '';
  }

  /**
   * modifyException
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyException(string $input): string
  {
    throw new Exception('Exception for testing purposes.');
  }

  /**
   * modifyFuelType
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyFuelType(string $input): string
  {
    $input = strtolower($input);

    switch ($input) {
    case 'diesel fuel':
      return 'Diesel';
    case 'flex fuel':
      return 'Flex';
    case 'gasoline fuel':
      return 'Gasoline';
    case 'hybrid fuel':
      return 'Hybrid';
    default:
      return 'Gasoline';
    }
  }

  /**
   * modifyKebab
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyKebab(string $input): string
  {
    return Str::kebab($input);
  }

  /**
   * modifyLimit
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyLimit(string $input, array $params): string
  {
    $limit = isset($params[0]) ? (int)$params[0] : 100;
    $end   = isset($params[1]) ? $params[1] : '...';

    if ($end === 'false') {
      $end = '';
    }

    return Str::limit($input, $limit, $end);
  }

  /**
   * modifyLower
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyLower(string $input, array $params): string
  {
    if (isset($params[0])) {
      $ignore = explode(',', $params[0]);

      if (in_array($input, $ignore)) {
        return $input;
      }
    }

    return strtolower($input);
  }

  /**
   * modifyMatch
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyMatch(string $input, array $params): string
  {
    if (!isset($params[0])) {
      return $input;
    }

    $pattern = trim($params[0], "'");
    $pattern = str_replace('%CN%', ':', $pattern);
    $pattern = str_replace('%LB%', '[', $pattern);
    $pattern = str_replace('%RB%', ']', $pattern);

    $index = isset($params[1]) ? (int)$params[1] : 0;

    preg_match($pattern, $input, $matches);

    if (isset($matches[$index])) {
      return $matches[$index];
    }

    return '';
  }

  /**
   * modifyNumber
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyNumber(string $input, array $params): string
  {
    $decimals = isset($params[0]) ? (int)$params[0] : 0;

    // 2017-11-28: The number modifier needs to be able to handle currencies
    // and their symbols. Therefor, we need to strip out any character that
    // might cause an issue when casting `$input` to a float.
    $number = preg_replace('/[^\d|\.]/iu', '', $input);
    $number = (float)$number;

    return number_format($number, $decimals);
  }

  /**
   * modifyPhone
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyPhone(string $input, array $params): string
  {
    $phone = preg_replace('/[^\d]/', '', $input);

    if (strlen($phone) === 11) {
      return $phone;
    }

    $countryCode = isset($params[0]) ? $params[0] : '1';
    $areaCode    = isset($params[1]) ? $params[1] : '000';

    if (strlen($phone) === 10) {
      return sprintf('%s%s', $countryCode, $phone);
    }

    if (strlen($phone) === 7) {
      return sprintf('%s%s%s', $countryCode, $areaCode, $phone);
    }

    return '';
  }

  /**
   * modifyPick
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyPick(string $input, array $params): string
  {
    if (count($params) < 2) {
      return $input;
    }

    $delimiter = trim($params[0]);

    switch ($delimiter) {
    case '%SP%':
      $delimiter = ' ';
      break;
    }

    $inputs = explode($delimiter, $input);

    $index = intval($params[1]);

    if ($index < 0) {
      $index = (count($inputs) + $index);
    }

    return isset($inputs[$index]) ? $inputs[$index] : $input;
  }

  /**
   * modifyPiped
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyPiped(string $input, array $params): string
  {
    if (count($params) < 1) {
      return $input;
    }

    $index = intval($params[0]);

    return $this->modifyPick($input, ['|', $index]);
  }

  /**
   * modifyPlural
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyPlural(string $input): string
  {
    return Str::plural($input);
  }

  /**
   * modifyPostal
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyPostal(string $input, array $params): string
  {
    $postal = trim($input);

    $country = isset($params[0]) ? strtoupper($params[0]) : 'US';

    switch ($country) {
    case 'CA':
      $isValid = (bool)preg_match('/^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])([\ ])?(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$/iu', $postal);
      break;
    case 'US':
      $isValid = (bool)preg_match('/^([0-9]{5})(-[0-9]{4})?$/iu', $postal);
      break;
    default:
      $isValid = false;
      break;
    }

    if (!$isValid) {
      return '';
    }

    // DEPRECATED 2017-11-17: If a postal code gets through the validation
    // we can just pass it along. No need to sanitize further.
    // $postal = preg_replace('/[^\d]/', '', $input);

    return $postal;
  }

  /**
   * modifyPrice
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyPrice(string $input): string
  {
    $number = $this->modifyNumber($input, ['2']);

    switch ($number) {
    case '0':
    case '0.0':
    case '0.00':
      return '';
    }

    return $number;
  }

  /**
   * modifyReplace
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyReplace(string $input, array $params): string
  {
    if (!isset($params[0]) || !isset($params[1])) {
      return $input;
    }

    $pattern = trim($params[0], "'");
    $pattern = str_replace('%CN%', ':', $pattern);
    $pattern = str_replace('%LB%', '[', $pattern);
    $pattern = str_replace('%LP%', '(', $pattern);
    $pattern = str_replace('%PI%', '|', $pattern);
    $pattern = str_replace('%RB%', ']', $pattern);
    $pattern = str_replace('%RP%', ')', $pattern);

    $replacement = trim($params[1], "'");

    return preg_replace($pattern, $replacement, $input);
  }

  /**
   * modifySingular
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifySingular(string $input): string
  {
    return Str::singular($input);
  }

  /**
   * modifySlug
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifySlug(string $input): string
  {
    return Str::slug($input);
  }

  /**
   * modifySnake
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifySnake(string $input): string
  {
    return Str::snake($input);
  }

  /**
   * modifyStudly
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyStudly(string $input): string
  {
    return Str::studly($input);
  }

  /**
   * modifySubstr
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifySubstr(string $input, array $params): string
  {
    if (count($params) < 1) {
      return $input;
    }

    $start  = isset($params[0]) ? (int)$params[0] : 0;
    $length = isset($params[1]) ? (int)$params[1] : null;

    return Str::substr($input, $start, $length);
  }

  /**
   * modifyTimeToDecimal
   *
   * As a general rule, we need to standardize what is returned from a modifier
   * when it is invalid. Here we return the origin input, but maybe we should
   * return and empty result instead.
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyTimeToDecimal(string $input): string
  {
    if (!preg_match('/^(\d+)?:\d{2}$/iu', $input)) {
      return $input;
    }

    $parts = explode(':', $input);

    $h = (int)$parts[0];
    $m = (int)$parts[1];

    if ($m < 0 || $m > 60) {
      return $input;
    }

    $decimal = $h + round($m / 60, 2);

    return number_format($decimal, 2);
  }

  /**
   * modifyTitle
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyTitle(string $input, array $params): string
  {
    if (isset($params[0])) {
      $ignore = explode(',', $params[0]);

      if (in_array($input, $ignore)) {
        return $input;
      }
    }

    return mb_convert_case($input, MB_CASE_TITLE, 'UTF-8');
  }

  /**
   * modifyTransmission
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyTransmission(string $input): string
  {
    $input = strtolower($input);

    switch ($input) {
    case 'automatic':
    case 'manual':
      return $input;
    default:
      return 'automatic';
    }
  }

  /**
   * modifyTrim
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyTrim(string $input): string
  {
    return trim($input);
  }

  /**
   * modifyUcfirst
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyUcfirst(string $input): string
  {
    return Str::ucfirst($input);
  }

  /**
   * modifyUpper
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyUpper(string $input, array $params): string
  {
    if (isset($params[0])) {
      $ignore = explode(',', $params[0]);

      if (in_array($input, $ignore)) {
        return $input;
      }
    }

    return strtoupper($input);
  }

  /**
   * modifyUrl
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyUrl(string $input): string
  {
    $url = urldecode($input);
    $url = trim($url);
    $url = trim($url, '/');

    return $url;
  }

  /**
   * modifyUrldecode
   *
   * @param string $input
   *
   * @return string
   */
  protected function modifyUrldecode(string $input): string
  {
    return urldecode($input);
  }

  /**
   * modifyWords
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyWords(string $input, array $params): string
  {
    if (count($params) < 1) {
      return $input;
    }

    $words = isset($params[0]) ? (int)$params[0] : 100;
    $end   = isset($params[1]) ? $params[1] : '...';

    if ($words < 1) {
      return '';
    }

    if ($end === 'false') {
      $end = '';
    }

    preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $input, $matches);

    if (!isset($matches[0])) {
      return $input;
    }

    if (mb_strlen($input) === mb_strlen($matches[0])) {
      return $input;
    }

    return rtrim($matches[0]).$end;
  }

  /**
   * modifyYear
   *
   * @param string $input
   * @param array  $params
   *
   * @return string
   */
  protected function modifyYear(string $input, array $params): string
  {
    if (!preg_match('/^\\d\\d(\\d\\d)?$/iu', $input)) {
      return '';
    }

    $digits = isset($params[0]) ? (int)$params[0] : 0;

    if (!$digits) {
      return $input;
    }

    if (mb_strlen($input) === $digits) {
      return $input;
    }

    if ($digits === 2) {
      return substr($input, -2);
    }

    $cutoff = isset($params[1]) ? (int)$params[1] : (int)date('y');

    if ((int)$input <= $cutoff) {
      return '20'.$input;
    } else {
      return '19'.$input;
    }
  }

  /**
   * replaceBoolean
   *
   * @param array $expression
   * @param array $data
   *
   * @return string
   */
  protected function replaceBoolean($expression, $data)
  {
    $parts = $expression['parts'];

    $condition  = $expression['parts'][0];
    $conditions = explode('=', $condition);

    if (empty($conditions[0]) || empty($conditions[1])) {
      // TODO: Log this to improve edge cases.
      return '';
    }

    $replace1 = $this->replaceSimple([
      'expression' => $conditions[0],
      'options'    => $this->getOptions($conditions[0]),
      'type'       => 'simple',
    ], $data);

    $replace2 = $this->replaceSimple([
      'expression' => $conditions[1],
      'options'    => $this->getOptions($conditions[1]),
      'type'       => 'simple',
    ], $data);

    $replace3 = $this->replaceSimple([
      'expression' => $parts[1],
      'options'    => $this->getOptions($parts[1]),
      'type'       => 'simple',
    ], $data);

    $replace4 = $this->replaceSimple([
      'expression' => $parts[2],
      'options'    => $this->getOptions($parts[2]),
      'type'       => 'simple',
    ], $data);

    return ($replace1 === $replace2) ? $replace3 : $replace4;
  }

  /**
   * replaceCalc
   *
   * @param array $expression
   * @param array $data
   *
   * @return string
   */
  protected function replaceCalc($expression, $data)
  {
    $parts = $expression['parts'];

    $expressions = $expression['parts'][1];
    $expressions = explode(',', $expressions);

    if (empty($expressions[0]) || empty($expressions[1])) {
      // TODO: Log this to improve edge cases.
      return '';
    }

    $value1 = $this->replaceSimple([
      'expression' => $expressions[0],
      'options'    => $this->getOptions($expressions[0]),
      'type'       => 'simple',
    ], $data);

    $value2 = $this->replaceSimple([
      'expression' => $expressions[1],
      'options'    => $this->getOptions($expressions[1]),
      'type'       => 'simple',
    ], $data);

    switch ($parts[0]) {
    case 'add':
      return (string)($value1 + $value2);
    case 'div':
      return (string)($value1 / $value2);
    case 'mul':
      return (string)($value1 * $value2);
    case 'sub':
      return (string)($value1 - $value2);
    }
  }

  /**
   * replaceSimple
   *
   * @param array $expression
   * @param array $data
   *
   * @return string
   */
  protected function replaceSimple($expression, $data)
  {
    $replace = '';

    foreach ($expression['options'] as $option) {
      if ($replace) {
        break;
      }

      $property = $option['property'];

      if (preg_match('/^"(.+)"$/u', $property, $matches)) {
        $replace = $matches[1];
      } else {
        $property = strtolower(trim($property));
        $replace  = $data[$property] ?? '';
      }

      foreach ($option['modifiers'] as $modifier) {
        $replace = $this->modify($replace, $modifier);
      }
    }

    return $replace;
  }

  // Static Methods
  // ---------------------------------------------------------------------------

  /**
   * Sanitizes raw data.
   *
   * @param array $data
   *
   * @return array
   */
  public static function sanitizeData($data): array
  {
    if (!is_array($data)) {
      return [];
    }

    $sanitized = [];

    foreach ($data as $key => $value) {
      $sanitized[strtolower(trim($key))] = $value;
    }

    return $sanitized;
  }

  /**
   * Sanitizes a raw expression.
   *
   * @param string $expression
   *
   * @return string
   */
  public static function sanitizeExpression($expression): string
  {
    if (!is_string($expression)) {
      return '';
    }

    $sanitized = trim($expression);

    return $sanitized;
  }

  /**
   * Sanitizes a raw field.
   *
   * @param string $field
   *
   * @return string
   */
  public static function sanitizeField($field): string
  {
    if (!is_string($field)) {
      return '';
    }

    $sanitized = trim($field);

    // Simple...
    $sanitized = preg_replace('/\[\s*/u',       '[',  $sanitized); // Remove spaces after "[".
    $sanitized = preg_replace('/\s*\]/u',       ']',  $sanitized); // Remove spaces before "]".
    $sanitized = preg_replace('/\s*-\s*>\s*/u', '->', $sanitized); // Remove spaces in and around "->".
    $sanitized = preg_replace('/\s*\|\s*/u',    '|',  $sanitized); // Remove spaces around "|".
    $sanitized = preg_replace('/\s*:\s*/u',     ':',  $sanitized); // Remove spaces around ":".

    // Boolean...
    $sanitized = preg_replace('/\s*\(\s*/u', '(', $sanitized); // Remove spaces around "(".
    $sanitized = preg_replace('/\s*\)\s*/u', ')', $sanitized); // Remove spaces around ")".
    $sanitized = preg_replace('/\s*=\s*/u',  '=', $sanitized); // Remove spaces around "=".
    $sanitized = preg_replace('/\s*{\s*/u',  '{', $sanitized); // Remove spaces around "{".
    $sanitized = preg_replace('/\s*}\s*/u',  '}', $sanitized); // Remove spaces around "}".

    // Literals...
    $sanitized = str_replace('%FLP%', '(', $sanitized);
    $sanitized = str_replace('%FRP%', ')', $sanitized);

    return $sanitized;
  }

  /**
   * Convert the given string to lower-case.
   *
   * @param string $value
   *
   * @return string
   */
  public static function toLower($value)
  {
    return mb_strtolower($value, 'UTF-8');
  }

  /**
   * Convert the given string to upper-case.
   *
   * @param string $value
   *
   * @return string
   */
  protected static function toUpper($value)
  {
    return mb_strtoupper($value, 'UTF-8');
  }
}
