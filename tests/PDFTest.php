<?php

use Mockery as m;

class PDFTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var CanGelis\PDF\PDF
	 */
	protected $pdf;

	/**
	 * Call private and protected methods
	 *
	 * @param string $methodName
	 * @param array  $args
	 *
	 * @return mixed
	 */
	protected function call($methodName, $args = array())
	{
		$method = new \ReflectionMethod($this->pdf, $methodName);
		$method->setAccessible(true);
		return $method->invokeArgs($this->pdf, $args);
	}

	public function setUp()
	{
		$this->pdf = new \CanGelis\PDF\PDF('echo', sys_get_temp_dir());
	}

	public function tearDown()
	{
		m::close();
	}

	public function testGetParamsReturnsParameterNamesCorrectly()
	{
		$this->call('addParam', array('foo'));
		$this->call('addParam', array('bar', 'baz'));
		$this->call('addParam', array('bazzer'));
		$this->assertEquals('--foo --bar "baz" --bazzer ', $this->call('getParams'));
	}

	public function testMethodNameConvertedToSnakeCaseParameter()
	{
		$this->assertEquals('foo-bar', $this->call('methodToParam', array('fooBar')));
		$this->assertEquals('foo', $this->call('methodToParam', array('foo')));
	}

	public function testInputSourceIsUrlWhenLoadUrlIsCalled()
	{
		$this->pdf->loadUrl('http://www.foo.bar');
		$this->assertEquals('http://www.foo.bar', $this->call('getInputSource'));
	}

	public function testInputSourceReturnsHTMLFileWhenLoadHtmlIsCalled()
	{
		$this->pdf->loadHTML('<b>foo bar</b>');
		$this->assertContains('.html', $this->call('getInputSource'));
	}

	/**
	 * @expectedException CanGelis\PDF\PDFException
	 */
	public function testGeneratePdfThrowsExceptionWhenAnErrorOccured()
	{
		$mock = m::mock('CanGelis\PDF\PDF[executeCommand]', array('echo', sys_get_temp_dir()));
		$mock->shouldReceive('executeCommand')->once()->andReturn(1);
		$mock->generatePDF();
	}

	public function testGeneratePdfGeneratesThePdf()
	{
		$mock = m::mock('CanGelis\PDF\PDF[executeCommand,removeTmpFiles,getPDFContents]', array('echo', sys_get_temp_dir()));
		$mock->shouldReceive('executeCommand')->once()->andReturn(0);
		$mock->shouldReceive('removeTmpFiles')->once();
		$mock->shouldReceive('getPDFContents')->once()->andReturn("");
		$mock->generatePDF();
	}

	public function testParamsAddedUsingTheMethods()
	{
		$this->pdf->lowquality()->marginTop('5mm');
		$this->assertEquals('--lowquality --margin-top "5mm" ', $this->call('getParams'));
	}

	/**
	 * @expectedException CanGelis\PDF\PDFException
	 */
	public function testUnknownMethodThrowsException()
	{
		$this->pdf->fooBar();
	}


}