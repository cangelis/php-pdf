<?php

use Mockery as m;

class PDFTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CanGelis\PDF\PDF
     */
    protected $pdf;

    /**
     * Call private and protected methods.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return mixed
     */
    protected function call($methodName, $args = [])
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
        $this->call('addParam', ['foo']);
        $this->call('addParam', ['bar', 'baz']);
        $this->call('addParam', ['bazzer']);
        $this->assertEquals('--foo --bar "baz" --bazzer ', $this->call('getParams'));
    }

    public function testMethodNameConvertedToSnakeCaseParameter()
    {
        $this->assertEquals('foo-bar', $this->call('methodToParam', ['fooBar']));
        $this->assertEquals('foo', $this->call('methodToParam', ['foo']));
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
        $mock = m::mock('CanGelis\PDF\PDF[executeCommand]', ['echo', sys_get_temp_dir()]);
        $mock->shouldReceive('executeCommand')->once()->andReturn(1);
        $mock->generate();
    }

    public function testGeneratePdfGeneratesThePdf()
    {
        $mock = m::mock('CanGelis\PDF\PDF[executeCommand,removeTmpFiles,getPDFContents]', ['echo', sys_get_temp_dir()]);
        $mock->shouldReceive('executeCommand')->once()->andReturn(0);
        $mock->shouldReceive('removeTmpFiles')->once();
        $mock->shouldReceive('getPDFContents')->once()->andReturn('');
        $mock->generate();
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
