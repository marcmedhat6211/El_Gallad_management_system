<?php

namespace PN\Bundle\CurrencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use PN\ServiceBundle\Model\DateTimeTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("exchange_rate", uniqueConstraints={
 *     @UniqueConstraint(name="exchange_unique", columns={"source_currency_id", "target_currency_id"})
 *     }
 *  )
 * @ORM\Entity(repositoryClass="PN\Bundle\CurrencyBundle\Repository\ExchangeRateRepository")
 */
class ExchangeRate
{
    use DateTimeTrait;

    const EGP = 1;
    const USD = 2;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    protected $sourceCurrency;

    /**
     * @ORM\ManyToOne(targetEntity="Currency")
     */
    protected $targetCurrency;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="ratio", type="float")
     */
    protected $ratio;

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->setModified(new \DateTime(date('Y-m-d H:i:s')));

        if ($this->getCreated() == null) {
            $this->setCreated(new \DateTime(date('Y-m-d H:i:s')));
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRatio(): ?string
    {
        return $this->ratio;
    }

    public function setRatio(string $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    public function getSourceCurrency(): ?Currency
    {
        return $this->sourceCurrency;
    }

    public function setSourceCurrency(?Currency $sourceCurrency): self
    {
        $this->sourceCurrency = $sourceCurrency;

        return $this;
    }

    public function getTargetCurrency(): ?Currency
    {
        return $this->targetCurrency;
    }

    public function setTargetCurrency(?Currency $targetCurrency): self
    {
        $this->targetCurrency = $targetCurrency;

        return $this;
    }



}
