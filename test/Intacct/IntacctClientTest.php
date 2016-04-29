<?php

/*
 * Copyright 2016 Intacct Corporation.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"). You may not
 * use this file except in compliance with the License. You may obtain a copy
 * of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "LICENSE" file accompanying this file. This file is distributed on
 * an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Intacct\Tests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Intacct\IntacctClientInterface;
use Intacct\IntacctClient;
use Intacct\Xml\Request\Operation\Content\Record;
use DOMDocument;

class IntacctClientTest extends XMLTestCase
{
    
    /**
     *
     * @var IntacctClientInterface
     */
    private $client;

    /**
     * @var DOMDocument
     */
    private $domDoc;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //the IntacctClient constructor will always get a session id, so mock it
        $xml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<response>
      <control>
            <status>success</status>
            <senderid>testsenderid</senderid>
            <controlid>sessionProvider</controlid>
            <uniqueid>false</uniqueid>
            <dtdversion>3.0</dtdversion>
      </control>
      <operation>
            <authentication>
                  <status>success</status>
                  <userid>testuser</userid>
                  <companyid>testcompany</companyid>
                  <sessiontimestamp>2015-12-06T15:57:08-08:00</sessiontimestamp>
            </authentication>
            <result>
                  <status>success</status>
                  <function>getAPISession</function>
                  <controlid>getSession</controlid>
                  <data>
                        <api>
                              <sessionid>testSeSsionID..</sessionid>
                              <endpoint>https://p1.intacct.com/ia/xml/xmlgw.phtml</endpoint>
                        </api>
                  </data>
            </result>
      </operation>
</response>
EOF;
        $headers = [
            'Content-Type' => 'text/xml; encoding="UTF-8"',
        ];
        $mockResponse = new Response(200, $headers, $xml);
        $mock = new MockHandler([
            $mockResponse,
        ]);
        
        $this->client = new IntacctClient([
            'sender_id' => 'testsenderid',
            'sender_password' => 'pass123!',
            'company_id' => 'testcompany',
            'user_id' => 'testuser',
            'user_password' => 'testpass',
            'mock_handler' => $mock,
        ]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    public function getDomDocument()
    {
        return $this->domDoc;
    }

    private function setDomDocumet($domDoc)
    {
        $this->domDoc = $domDoc;
    }

    /**
     * @covers Intacct\IntacctClient::__construct
     * @covers Intacct\IntacctClient::getSessionCreds
     * @covers Intacct\IntacctClient::getLastExecution
     */
    public function testConstructWithSessionId()
    {
        $client = $this->client; //grab the setUp object
        
        $creds = $client->getSessionConfig();
        $this->assertEquals($creds['endpoint_url'], 'https://p1.intacct.com/ia/xml/xmlgw.phtml');
        $this->assertEquals($creds['session_id'], 'testSeSsionID..');
        $this->assertEquals(count($client->getLastExecution()), 1);
    }
    
    /**
     * @covers Intacct\IntacctClient::__construct
     * @covers Intacct\IntacctClient::getSessionCreds
     * @covers Intacct\IntacctClient::getLastExecution
     */
    public function testConstructWithLogin()
    {
        $xml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<response>
      <control>
            <status>success</status>
            <senderid>testsenderid</senderid>
            <controlid>sessionProvider</controlid>
            <uniqueid>false</uniqueid>
            <dtdversion>3.0</dtdversion>
      </control>
      <operation>
            <authentication>
                  <status>success</status>
                  <userid>testuser</userid>
                  <companyid>testcompany</companyid>
                  <sessiontimestamp>2015-12-06T15:57:08-08:00</sessiontimestamp>
            </authentication>
            <result>
                  <status>success</status>
                  <function>getAPISession</function>
                  <controlid>getSession</controlid>
                  <data>
                        <api>
                              <sessionid>helloworld..</sessionid>
                              <endpoint>https://p1.intacct.com/ia/xml/xmlgw.phtml</endpoint>
                        </api>
                  </data>
            </result>
      </operation>
</response>
EOF;
        $headers = [
            'Content-Type' => 'text/xml; encoding="UTF-8"',
        ];
        $mockResponse = new Response(200, $headers, $xml);
        $mock = new MockHandler([
            $mockResponse,
        ]);
        
        $client = new IntacctClient([
            'sender_id' => 'testsenderid',
            'sender_password' => 'pass123!',
            'session_id' => 'originalSeSsIonID..',
            'mock_handler' => $mock,
        ]);
        
        $creds = $client->getSessionConfig();
        $this->assertEquals($creds['endpoint_url'], 'https://p1.intacct.com/ia/xml/xmlgw.phtml');
        $this->assertEquals($creds['session_id'], 'helloworld..');
        $this->assertEquals(count($client->getLastExecution()), 1);
    }


    /**
     * @covers Intacct\Dimension\ClassObj::create
     * @covers Intacct\Xml\RequestHandler::executeContent
     * @covers Intacct\IntacctClient::getSessionConfig
     */
    public function testCreateSuccess()
    {
        $xml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<response>
      <control>
            <status>success</status>
            <senderid>testsenderid</senderid>
            <controlid>requestControlId</controlid>
            <uniqueid>false</uniqueid>
            <dtdversion>3.0</dtdversion>
      </control>
      <operation>
            <authentication>
                  <status>success</status>
                  <userid>testuser</userid>
                  <companyid>testcompany</companyid>
                  <sessiontimestamp>2016-01-24T14:26:56-08:00</sessiontimestamp>
            </authentication>
            <result>
                  <status>success</status>
                  <function>create</function>
                  <controlid>create</controlid>
                  <data listtype="objects" count="2">
                        <class>
                              <RECORDNO>5</RECORDNO>
                              <CLASSID>UT01</CLASSID>
                        </class>
                        <class>
                              <RECORDNO>6</RECORDNO>
                              <CLASSID>UT02</CLASSID>
                        </class>
                  </data>
            </result>
      </operation>
</response>
EOF;
        $headers = [
            'Content-Type' => 'text/xml; encoding="UTF-8"',
        ];
        $mockResponse = new Response(200, $headers, $xml);
        $mock = new MockHandler([
            $mockResponse,
        ]);

        $create = [
            'records' => [
                new Record([
                    'object' => 'CLASS',
                    'fields' => [
                        'CLASSID' => 'UT01',
                        'NAME' => 'Unit Test 01',
                    ],
                ]),
                new Record([
                    'object' => 'CLASS',
                    'fields' => [
                        'CLASSID' => 'UT02',
                        'NAME' => 'Unit Test 02',
                    ],
                ]),
            ],
            'mock_handler' => $mock,
        ];

        $data = $this->client->create($create);

        $request = $mock->getLastRequest();

        $requestXML = $request->getBody()->getContents();


        // Verify request XML through XPath
        $dom = new DomDocument();
        $dom->loadXML($requestXML);

        $this->setDomDocumet($dom);

        $this->assertXpathMatch('create',
            'name(/request/operation/content/function/*)',
            'function does not match');

        $this->assertXpathMatch('CLASS',
            'name(/request/operation/content/function/create/*)',
            'object does not match');

        $this->assertXpathMatch('UT01Unit Test 01',
            'string(/request/operation/content/function/create/*)',
            'object does not match');

        $this->assertEquals($data->getStatus(), 'success');
        $this->assertEquals($data->getFunction(), 'create');
        $this->assertEquals($data->getControlId(), 'create');

        // TO DO more testing??
    }
}
