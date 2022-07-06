class FluentCalculator
{
	const MAX_LEN = 9;
    protected $listArr = [];
    protected $operatorArr = ['plus'=>1,'minus'=>1,'times'=>1,'dividedBy'=>1,];
    protected $numberArr = ['zero'=>0,'one'=>1,'two'=>2,'three'=>3,'four'=>4,'five'=>5,'six'=>6,'seven'=>7,'eight'=>8,'nine'=>9,];
    protected $lastValIsOp = false;

    public static function init() {
        return new static();
    }

    public function __get($value) {
        if (isset($this->numberArr[$value])) {
            if (empty($this->listArr) || $this->lastValIsOp) {
                $this->listArr[] = '';
            }

            $this->lastValIsOp = false;
            $key = count($this->listArr) - 1;
            
            if (!empty($this->listArr[$key]) || $value !== '') {
                $this->listArr[$key] .= $this->numberArr[$value];
            }
        } else {
            if (!empty($this->operatorArr[$value])) {
                if ($this->lastValIsOp) {
                    $this->listArr[count($this->listArr) - 1] = $value;
                } else {
                    $this->listArr[] = $value;
                }

                $this->lastValIsOp = true;
            } else {
                throw new InvalidInputException();
            }
        }

        return $this;
    }

    public function __call($method, $args)
    {
        if (isset($this->numberArr[$method])) {
            if ($this->lastValIsOp) {
                $this->listArr[] = $this->numberArr[$method];
            } else {
                if (empty($this->listArr)) {
                    $this->listArr[] = '';
                }
                $this->listArr[count($this->listArr) - 1] .= $this->numberArr[$method];
            }
        } elseif (!empty($this->operatorArr[$method])) {
        } else {
            throw new InvalidInputException();
        }

        $response = 0;
        $first_run = true;

        while ($this->listArr) {
            $cur = array_shift($this->listArr);
            if (!empty($this->operatorArr[$cur])) {
                if (empty($this->listArr)) {
                    continue;
                }

                $oper = array_shift($this->listArr);
                if (strlen(abs($oper)) > self::MAX_LEN) {
                    throw new DigitCountOverflowException();
                }

                switch ($cur) {
                    case 'plus':
                        $response += $oper;
                        break;
                    case 'minus':
                        if ($first_run) {
                            $response = -1 * $oper;
                        } else {
                            $response -= $oper;
                        }
                        break;
                    case 'times':
                        $response *= $oper;
                        break;
                    case 'dividedBy':
                        if (0 == $oper) { throw new DivisionByZeroException(); }
                        $response = intdiv($response, $oper);
                        break;
                }
            } else {
                $response = (int)$cur;
            }
            if (strlen(abs($response)) > self::MAX_LEN) { throw new DigitCountOverflowException(); }
            $first_run = false;
        }
        return $response;
    }
}
