<?php
/**
 * So painful.
 */
class Mail_QueueTest extends Mail_QueueAbstract
{
    public function testPut()
    {
        $id_user      = 1;
        $ip           = '127.0.0.1';
        $sender       = 'testsuite@example.org';
        $recipient    = 'testcase@example.org';
        $headers      = array('X-TestSuite' => 1);
        $body         = 'Lorem ipsum';

        $mailId = $this->queue->put($sender, $recipient, $headers, $body, 0, true, $id_user);
        if (!is_numeric($mailId)) {
            $this->handlePearError($mailId, "Could not save email.");
        }

        $this->assertEquals(1, $mailId); // it's the first email, after all :-)
        $this->assertEquals(1, count($this->queue->getQueueCount()));
    }

    /**
     * This should return a MDB2_Error
     *
     * @expectedException PEAR2\Mail\Queue\Exception
     */
    public function testSendMailByIdWithInvalidId()
    {
        $randomId = rand(1, 12);
        $status   = $this->queue->sendMailById($randomId);
    }

    /**
     * Queue two emails - to be send right away.
     *
     * @return void
     */
    public function testSendMailsInQueue()
    {
        $id_user      = 1;
        $sender       = 'testsuite@example.org';
        $recipient    = 'testcase@example.org';
        $headers      = array('X-TestSuite' => 1);
        $body         = 'Lorem ipsum';

        $mailId1 = $this->queue->put($sender, $recipient, $headers, $body);
        if ($mailId1 instanceof PEAR_Error) {
            $this->fail("Queueing first mail failed: {$mailId1->getMessage()}");
            return;
        }

        $id_user      = 1;
        $sender       = 'testsuite@example.org';
        $recipient    = 'testcase@example.org';
        $headers      = array('X-TestSuite' => 2);
        $body         = 'Lorem ipsum sit dolor';

        $mailId2 = $this->queue->put($sender, $recipient, $headers, $body);
        if ($mailId2 instanceof PEAR_Error) {
            $this->fail("Queueing first mail failed: {$mailId2->getMessage()}");
            return;
        }

        $queueCount = $this->queue->getQueueCount();
        if ($this->queue->hasErrors()) {
            $fail = '';
            foreach ($this->queue->getErrors() as $error) {
                $fail .= $error->getMessage() . ", ";
            }
            $this->fail("Errors from getQueueCount: {$fail}");
            return;
        }
        $this->assertEquals(2, $queueCount, "Failed to count 2 messages.");

        $status = $this->queue->sendMailsInQueue();
        if ($status instanceof PEAR_Error) {
            $this->fail("Error sending emails: {$status->getMessage()}.");
            return;
        }

        $this->assertTrue($status);
        $this->assertEquals(0, $this->queue->getQueueCount(), "Mails did not get removed?");
    }
}
