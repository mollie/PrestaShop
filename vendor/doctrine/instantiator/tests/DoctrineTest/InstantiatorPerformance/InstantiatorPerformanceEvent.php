<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
namespace MolliePrefix\DoctrineTest\InstantiatorPerformance;

use MolliePrefix\Athletic\AthleticEvent;
use MolliePrefix\Doctrine\Instantiator\Instantiator;
/**
 * Performance tests for {@see \Doctrine\Instantiator\Instantiator}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class InstantiatorPerformanceEvent extends \MolliePrefix\Athletic\AthleticEvent
{
    /**
     * @var \Doctrine\Instantiator\Instantiator
     */
    private $instantiator;
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->instantiator = new \MolliePrefix\Doctrine\Instantiator\Instantiator();
        $this->instantiator->instantiate(__CLASS__);
        $this->instantiator->instantiate('ArrayObject');
        $this->instantiator->instantiate('MolliePrefix\\DoctrineTest\\InstantiatorTestAsset\\SimpleSerializableAsset');
        $this->instantiator->instantiate('MolliePrefix\\DoctrineTest\\InstantiatorTestAsset\\SerializableArrayObjectAsset');
        $this->instantiator->instantiate('MolliePrefix\\DoctrineTest\\InstantiatorTestAsset\\UnCloneableAsset');
    }
    /**
     * @iterations 20000
     * @baseline
     * @group instantiation
     */
    public function testInstantiateSelf()
    {
        $this->instantiator->instantiate(__CLASS__);
    }
    /**
     * @iterations 20000
     * @group instantiation
     */
    public function testInstantiateInternalClass()
    {
        $this->instantiator->instantiate('ArrayObject');
    }
    /**
     * @iterations 20000
     * @group instantiation
     */
    public function testInstantiateSimpleSerializableAssetClass()
    {
        $this->instantiator->instantiate('MolliePrefix\\DoctrineTest\\InstantiatorTestAsset\\SimpleSerializableAsset');
    }
    /**
     * @iterations 20000
     * @group instantiation
     */
    public function testInstantiateSerializableArrayObjectAsset()
    {
        $this->instantiator->instantiate('MolliePrefix\\DoctrineTest\\InstantiatorTestAsset\\SerializableArrayObjectAsset');
    }
    /**
     * @iterations 20000
     * @group instantiation
     */
    public function testInstantiateUnCloneableAsset()
    {
        $this->instantiator->instantiate('MolliePrefix\\DoctrineTest\\InstantiatorTestAsset\\UnCloneableAsset');
    }
}
