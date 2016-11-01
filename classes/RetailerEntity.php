<?php
class RetailerEntity implements JsonSerializable
{
    protected $id;
    protected $name;
    protected $api_url;
    protected $active;
    protected $locations;
    /**
     * Accept an array of data matching properties of this class
     * and create the class
     *
     * @param array $data The data to use to create
     */
    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->api_url = $data['api_url'];
        $this->active = $data['active'];
        $this->locations = $data['locations'];
    }
    public function getId() {
        return $this->id;
    }
    public function getName() {
        return $this->name;
    }
    public function getURL() {
        return $this->api_url;
    }
    public function getActive() {
        return $this->active;
    }
    public function getLocations() {
        return $this->locations;
    }

	public function jsonSerialize()
    {
		 return [
            'id'	=> $this->getId(),
            'name' 	=> $this->getName(),
            'api_url'	=> $this->getURL(),
            'active'	=> $this->getActive(),
            'locations'	=> $this->getLocations()
        ];
    }
}
