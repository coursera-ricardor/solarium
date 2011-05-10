<?php
/**
 * Copyright 2011 Bas de Nooijer. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this listof conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are
 * those of the authors and should not be interpreted as representing official
 * policies, either expressed or implied, of the copyright holder.
 */

class Solarium_Client_RequestBuilder_SelectTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Solarium_Query_Select
     */
    protected $_query;

    /**
     * @var Solarium_Client_RequestBuilder_Select
     */
    protected $_builder;

    public function setUp()
    {
        $this->_query = new Solarium_Query_Select;
        $this->_builder = new Solarium_Client_RequestBuilder_Select;
    }

    public function testGetMethod()
    {
        $request = $this->_builder->build($this->_query);
        $this->assertEquals(
            Solarium_Client_Request::METHOD_GET,
            $request->getMethod()
        );
    }

    public function testSelectUrlWithDefaultValues()
    {
        $request = $this->_builder->build($this->_query);

        $this->assertEquals(
            null,
            $request->getRawData()
        );

        $this->assertEquals(
            'select?q=*:*&start=0&rows=10&fl=*,score&wt=json',
            urldecode($request->getUri())
        );
    }

    public function testSelectUrlWithSort()
    {
        $this->_query->addSort('id', Solarium_Query_Select::SORT_ASC);
        $this->_query->addSort('name', Solarium_Query_Select::SORT_DESC);
        $request = $this->_builder->build($this->_query);

        $this->assertEquals(
            null,
            $request->getRawData()
        );

        $this->assertEquals(
            'select?q=*:*&start=0&rows=10&fl=*,score&wt=json&sort=id asc,name desc',
            urldecode($request->getUri())
        );
    }

    public function testSelectUrlWithSortAndFilters()
    {
        $this->_query->addSort('id', Solarium_Query_Select::SORT_ASC);
        $this->_query->addSort('name', Solarium_Query_Select::SORT_DESC);
        $this->_query->addFilterQuery(new Solarium_Query_Select_FilterQuery(array('key' => 'f1', 'query' => 'published:true')));
        $this->_query->addFilterQuery(new Solarium_Query_Select_FilterQuery(array('key' => 'f2', 'tag' => array('t1','t2'), 'query' => 'category:23')));
        $request = $this->_builder->build($this->_query);

        $this->assertEquals(
            null,
            $request->getRawData()
        );

        $this->assertEquals(
            'select?q=*:*&start=0&rows=10&fl=*,score&wt=json&sort=id asc,name desc&fq=published:true&fq={!tag=t1,t2}category:23',
            urldecode($request->getUri())
        );
    }

    public function testSelectUrlWithFacets()
    {
        $this->_query->getFacetSet()->addFacet(new Solarium_Query_Select_Component_Facet_Field(array('key' => 'f1', 'field' => 'owner')));
        $this->_query->getFacetSet()->addFacet(new Solarium_Query_Select_Component_Facet_Query(array('key' => 'f2', 'query' => 'category:23')));
        $this->_query->getFacetSet()->addFacet(new Solarium_Query_Select_Component_Facet_MultiQuery(array('key' => 'f3', 'query' => array('f4' => array('query' => 'category:40')))));
        $request = $this->_builder->build($this->_query);

        $this->assertEquals(
            null,
            $request->getRawData()
        );

        $this->assertEquals(
            'select?q=*:*&start=0&rows=10&fl=*,score&wt=json&facet=true&facet.field={!key=f1}owner&facet.query={!key=f2}category:23&facet.query={!key=f4}category:40',
            urldecode($request->getUri())
        );
    }

    public function testSelectUrlWithRangeFacet()
    {
        $this->_query->getFacetSet()->addFacet(new Solarium_Query_Select_Component_Facet_Range(
            array(
                'key' => 'f1',
                'field' => 'price',
                'start' => '1',
                'end' => 100,
                'gap' => 10,
                'other' => 'all',
                'include' => 'outer'
            )
        ));

        $request = $this->_builder->build($this->_query);

        $this->assertEquals(
            null,
            $request->getRawData()
        );

        $this->assertEquals(
            'select?q=*:*&start=0&rows=10&fl=*,score&wt=json&facet=true&facet.range={!key=f1}price&f.price.facet.range.start=1&f.price.facet.range.end=100&f.price.facet.range.gap=10&f.price.facet.range.other=all&f.price.facet.range.include=outer',
            urldecode($request->getUri())
        );
    }

    public function testSelectUrlWithFacetsAndGlobalFacetSettings()
    {
        $this->_query->getFacetSet()->setMissing(true);
        $this->_query->getFacetSet()->setLimit(10);
        $this->_query->getFacetSet()->addFacet(new Solarium_Query_Select_Component_Facet_Field(array('key' => 'f1', 'field' => 'owner')));
        $this->_query->getFacetSet()->addFacet(new Solarium_Query_Select_Component_Facet_Query(array('key' => 'f2', 'query' => 'category:23')));
        $this->_query->getFacetSet()->addFacet(new Solarium_Query_Select_Component_Facet_MultiQuery(array('key' => 'f3', 'query' => array('f4' =>array('query' => 'category:40')))));
        $request = $this->_builder->build($this->_query);

        $this->assertEquals(
            null,
            $request->getRawData()
        );

        $this->assertEquals(
            'select?q=*:*&start=0&rows=10&fl=*,score&wt=json&facet=true&facet.missing=1&facet.limit=10&facet.field={!key=f1}owner&facet.query={!key=f2}category:23&facet.query={!key=f4}category:40',
            urldecode($request->getUri())
        );
    }

    public function testUnknownFacetType()
    {
        $this->_query->getFacetSet()->addFacet(new UnknownFacet(array('key' => 'f1', 'field' => 'owner')));
        $this->setExpectedException('Solarium_Exception');
        $request = $this->_builder->build($this->_query);
        $request->getUri();
    }
    
}

class UnknownFacet extends Solarium_Query_Select_Component_Facet_Field{

    public function getType()
    {
        return 'unknown';
    }


}