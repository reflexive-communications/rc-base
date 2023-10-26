<?php

namespace Civi\RcBase\Exception;

use Civi\RcBase\HeadlessTestCase;
use CRM_Core_Error;
use CRM_Core_Exception;

/**
 * @group headless
 */
class ExceptionTest extends HeadlessTestCase
{
    /**
     * @return void
     */
    public function testLogException()
    {
        $extension = 'test-extension';
        $previous_exception_msg = 'some previous test exception';
        $reason = 'Duplicate email address';

        try {
            $prev = new CRM_Core_Exception($previous_exception_msg);
            throw new APIException('Contact', 'create', $reason, $prev);
        } catch (BaseException $ex) {
            BaseException::logException($extension, $ex);
        }

        // Read last 25 lines from log file
        $file_log = CRM_Core_Error::createDebugLogger();
        $fp = fopen($file_log->_filename, 'r');
        $lines = [];
        while (!feof($fp)) {
            $lines[] = fgets($fp);
            if (count($lines) > 25) {
                array_shift($lines);
            }
        }
        fclose($fp);

        $log = implode('', $lines);
        self::assertStringContainsString("[${extension}]", $log, 'Extension name not found in log');
        self::assertStringContainsString($previous_exception_msg, $log, 'Previous exception not found in log');
        self::assertStringContainsString($reason, $log, 'Reason not found in log');
        self::assertStringContainsString(APIException::ERROR_CODE, $log, 'Error code not found in log');
    }

    /**
     * @return void
     */
    public function testInvalidArgumentException()
    {
        $argument = 'contact ID';
        $exception = new InvalidArgumentException($argument);
        self::assertSame("Invalid {$argument}", $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(InvalidArgumentException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'must be positive';
        $prev = new CRM_Core_Exception('prev');
        $exception = new InvalidArgumentException($argument, $msg, $prev);
        self::assertSame("Invalid {$argument}: {$msg}", $exception->getMessage(), 'Wrong message returned');

        $expected_data = [
            'argument' => $argument,
            'error_code' => InvalidArgumentException::ERROR_CODE,
        ];
        self::assertSame($expected_data, $exception->getErrorData(), 'Wrong error data returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testMissingArgumentException()
    {
        $argument = 'contact ID';
        $exception = new MissingArgumentException($argument);
        self::assertSame("Missing {$argument}", $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(MissingArgumentException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'not possible to determine';
        $prev = new CRM_Core_Exception('prev');
        $exception = new MissingArgumentException($argument, $msg, $prev);
        self::assertSame("Missing {$argument}: {$msg}", $exception->getMessage(), 'Wrong message returned');

        $expected_data = [
            'argument' => $argument,
            'error_code' => MissingArgumentException::ERROR_CODE,
        ];
        self::assertSame($expected_data, $exception->getErrorData(), 'Wrong error data returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testNotFoundException()
    {
        $exception = new NotFoundException();
        self::assertSame('Not found', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(NotFoundException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'msg_id';
        $prev = new CRM_Core_Exception('prev');
        $exception = new NotFoundException($msg, $prev);
        self::assertSame("Not found: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testCorruptedDataException()
    {
        $exception = new CorruptedDataException();
        self::assertSame('Corrupted data found', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(CorruptedDataException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'missing contact ID';
        $prev = new CRM_Core_Exception('prev');
        $exception = new CorruptedDataException($msg, $prev);
        self::assertSame("Corrupted data found: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testDatabaseException()
    {
        $exception = new DataBaseException();
        self::assertSame('DataBase error occurred', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(DataBaseException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'missing contact ID';
        $prev = new CRM_Core_Exception('prev');
        $exception = new DataBaseException($msg, $prev);
        self::assertSame("DataBase error occurred: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testApiException()
    {
        $entity = 'Contact';
        $action = 'update';
        $exception = new APIException($entity, $action);
        self::assertSame("Failed to execute API: {$entity}.{$action}", $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(APIException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'missing contact ID';
        $prev = new CRM_Core_Exception('prev');
        $exception = new APIException($entity, $action, $msg, $prev);
        self::assertSame("Failed to execute API: {$entity}.{$action} Reason: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');

        $expected_data = [
            'entity' => $entity,
            'action' => $action,
            'error_code' => APIException::ERROR_CODE,
        ];
        self::assertSame($expected_data, $exception->getErrorData(), 'Wrong error data returned');
    }

    /**
     * @return void
     */
    public function testRunTimeException()
    {
        $exception = new RunTimeException();
        self::assertSame('Run-time error', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(RunTimeException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'stack overflow';
        $prev = new CRM_Core_Exception('prev');
        $exception = new RunTimeException($msg, $prev);
        self::assertSame("Run-time error: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testHttpException()
    {
        $exception = new HTTPException();
        self::assertSame('HTTP error', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(HTTPException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'bad request [400]';
        $prev = new CRM_Core_Exception('prev');
        $exception = new HTTPException($msg, $prev);
        self::assertSame("HTTP error: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testUnauthorizedException()
    {
        $exception = new UnauthorizedException();
        self::assertSame('Unauthorized', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(UnauthorizedException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'invalid key';
        $prev = new CRM_Core_Exception('prev');
        $exception = new UnauthorizedException($msg, $prev);
        self::assertSame("Unauthorized: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }

    /**
     * @return void
     */
    public function testIllegalException()
    {
        $exception = new IllegalException();
        self::assertSame('Illegal condition', $exception->getMessage(), 'Wrong message returned for empty message');
        self::assertSame(IllegalException::ERROR_CODE, $exception->getErrorCode(), 'Wrong error code returned');

        $msg = 'configuration error';
        $prev = new CRM_Core_Exception('prev');
        $exception = new IllegalException($msg, $prev);
        self::assertSame("Illegal condition: {$msg}", $exception->getMessage(), 'Wrong message returned');
        self::assertSame($prev, $exception->getPreviousException(), 'Wrong previous exception returned');
    }
}
