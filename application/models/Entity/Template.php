<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Template
 */
class Template
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $modifiedAt;

    /**
     * @var boolean
     */
    private $includeInSearch;

    /**
     * @var boolean
     */
    private $indexForSearching;

    /**
     * @var integer
     */
    private $templateColor;

    /**
     * @var boolean
     */
    private $isHidden;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $widgets;

    /**
     * @var \Entity\Template
     */
    private $source_template;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $instances;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->widgets = new \Doctrine\Common\Collections\ArrayCollection();
        $this->instances = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Template
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Template
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set modifiedAt
     *
     * @param \DateTime $modifiedAt
     * @return Template
     */
    public function setModifiedAt($modifiedAt)
    {
        $this->modifiedAt = $modifiedAt;

        return $this;
    }

    /**
     * Get modifiedAt
     *
     * @return \DateTime
     */
    public function getModifiedAt()
    {
        return $this->modifiedAt;
    }

    /**
     * Set includeInSearch
     *
     * @param boolean $includeInSearch
     * @return Template
     */
    public function setIncludeInSearch($includeInSearch)
    {
        $this->includeInSearch = $includeInSearch;

        return $this;
    }

    /**
     * Get includeInSearch
     *
     * @return boolean
     */
    public function getIncludeInSearch()
    {
        return $this->includeInSearch;
    }

    /**
     * Set indexForSearching
     *
     * @param boolean $indexForSearching
     * @return Template
     */
    public function setIndexForSearching($indexForSearching)
    {
        $this->indexForSearching = $indexForSearching;

        return $this;
    }

    /**
     * Get indexForSearching
     *
     * @return boolean
     */
    public function getIndexForSearching()
    {
        return $this->indexForSearching;
    }

    /**
     * Set templateColor
     *
     * @param integer $templateColor
     * @return Template
     */
    public function setTemplateColor($templateColor)
    {
        $this->templateColor = $templateColor;

        return $this;
    }

    /**
     * Get templateColor
     *
     * @return integer
     */
    public function getTemplateColor()
    {
        return $this->templateColor;
    }

    /**
     * Set isHidden
     *
     * @param boolean $isHidden
     * @return Template
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * Get isHidden
     *
     * @return boolean
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add widgets
     *
     * @param \Entity\Widget $widgets
     * @return Template
     */
    public function addWidget(\Entity\Widget $widgets)
    {
        $this->widgets[] = $widgets;

        return $this;
    }

    /**
     * Remove widgets
     *
     * @param \Entity\Widget $widgets
     */
    public function removeWidget(\Entity\Widget $widgets)
    {
        $this->widgets->removeElement($widgets);
    }

    /**
     * Get widgets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * Set source_template
     *
     * @param \Entity\Template $sourceTemplate
     * @return Template
     */
    public function setSourceTemplate(\Entity\Template $sourceTemplate = null)
    {
        $this->source_template = $sourceTemplate;

        return $this;
    }

    /**
     * Get source_template
     *
     * @return \Entity\Template
     */
    public function getSourceTemplate()
    {
        return $this->source_template;
    }

    /**
     * Add instances
     *
     * @param \Entity\Instance $instances
     * @return Template
     */
    public function addInstance(\Entity\Instance $instances)
    {
        $instances->addTemplate($this);
        $this->instances[] = $instances;

        return $this;
    }

    /**
     * Remove instances
     *
     * @param \Entity\Instance $instances
     */
    public function removeInstance(\Entity\Instance $instances)
    {
        $this->instances->removeElement($instances);
    }

    /**
     * Get instances
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInstances()
    {
        return $this->instances;
    }
    /**
     * @var boolean
     */
    private $showCollection;


    /**
     * Set showCollection
     *
     * @param boolean $showCollection
     *
     * @return Template
     */
    public function setShowCollection($showCollection)
    {
        $this->showCollection = $showCollection;

        return $this;
    }

    /**
     * Get showCollection
     *
     * @return boolean
     */
    public function getShowCollection()
    {
        return $this->showCollection;
    }
}
