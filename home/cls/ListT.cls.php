<?php
/*
This file is part of wTorrent.

wTorrent is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

wTorrent is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class ListT extends rtorrent
{
	private  $view;
	private  $MAX_LENGTH_NAME = 55;
 
  public function construct()
	{
		if($this->_request['view'] == 'public') $this->view = 'public';
		if($this->_request['view'] == 'private') $this->view = 'private';
		if(!isset($this->_request['view'])) $this->view = 'public';
		
		if(!$this->setClient())
			return false;
		
		/* d multicall with all the necessary info to generate the torrent list */
		$array_d = array('d.get_name', 'd.get_down_rate', 'd.get_up_rate', 'd.get_chunk_size','d.get_completed_chunks','d.get_size_chunks','d.get_state','d.get_peers_accounted','d.get_peers_complete','d.is_hash_checking','d.get_ratio','d.get_tracker_size','d.is_active','d.is_open','d.get_message','d.get_creation_date');
 		$this->multicall->d_multicall($array_d);
		// t multicall
		$array_t = array('t.get_scrape_complete', 't.get_scrape_incomplete');
		$hashes = $this->getHashes(); // Retrieve hashes
		foreach($hashes as $hash)
			$this->multicall->t_multicall($hash, $array_t);
		
		if(isset($this->_request['start'])) $this->start($this->_request['start']);
		if(isset($this->_request['stop'])) $this->stop($this->_request['stop']);
		if(isset($this->_request['erase'])) $this->erase($this->_request['erase']);
	}

	public function getView()
	{
		return $this->view;
	}
	public function getPublicHashes()
	{
		$hashes = $this->getHashes();
		foreach($hashes as $hash)
			if($this->torrents[$hash]->get_private() === false)
				$return[] = $hash;
				
		return $return;
	}
	public function getPublicHashesNum()
	{
		$hashes = $this->getHashes();
		$i = 0;
		foreach($hashes as $hash)
			if($this->torrents[$hash]->get_private() === false)
				$i++;
				
		return $i;
	}
	public function getPrivateHashes()
	{
		$hashes = $this->getHashes();
		foreach($hashes as $hash)
			if(($this->torrents[$hash]->get_private() === true) && ($this->torrents[$hash]->get_owner() == $this->getIdUser()))
				$return[] = $hash;
				
		return $return;
	}
	public function getPrivateHashesNum()
	{
		$hashes = $this->getHashes();
		$i = 0;
		foreach($hashes as $hash)
		if(($this->torrents[$hash]->get_private() === false) && ($this->torrents[$hash]->get_owner() == $this->getIdUser()))
			$i++;
				
		return $i;
	}
	public function getName($hash)
	{
		// Just in case the torrent name is too long return a shortened version. (THIS SHOULD BE DONE IN THE TEMPLATE WITH SMARTY TRUNCATE)
		$name = $this->torrents[$hash]->get_name();
		if(strlen($name) > $this->MAX_LENGTH_NAME){
			$name = substr($name, 0, $this->MAX_LENGTH_NAME) . ' ...';
		}
		return $name;
	}
	public function getState($hash)
	{
		return $this->torrents[$hash]->get_state();
	}
	public function getOpen($hash)
	{
		return $this->torrents[$hash]->is_open();
	}
	public function getConnPeers($hash)
	{
		return $this->torrents[$hash]->get_peers_accounted();
	}
	public function getConnSeeds($hash)
	{
		return $this->torrents[$hash]->get_peers_complete();
	}
	public function getTotalPeers($hash)
	{
		$peers = 0;
		$num = $this->torrents[$hash]->get_tracker_size();
		
		for($i = 0; $i < $num; $i++)
		{
			$t_peers = $this->torrents[$hash]->t_get_scrape_incomplete($i);
			$peers += $t_peers;
		}
			
		return $peers;
	}
	public function getTotalSeeds($hash)
	{
		$seeds = 0;
		$num = $this->torrents[$hash]->get_tracker_size();
		
		for($i = 0; $i < $num; $i++)
		{
			$t_seeds = $this->torrents[$hash]->t_get_scrape_complete($i);
			$seeds += $t_seeds;
		}
		
		return $seeds;
	}
	public function getDownRate($hash)
	{
		return round($this->torrents[$hash]->get_down_rate()/1024,2);
	}
	public function getUpRate($hash)
	{
		return round($this->torrents[$hash]->get_up_rate()/1024,2);
	}
	public function getPercent($hash)
	{
		return floor(($this->torrents[$hash]->get_completed_chunks()/$this->torrents[$hash]->get_size_chunks())*100);
	}
	public function getRatio($hash)
	{
		return round($this->torrents[$hash]->get_ratio()/1000,2);
	}
	public function isHashChecking($hash)
	{
		return $this->torrents[$hash]->is_hash_checking();
	}
	public function getETA($hash)
	{
		$return = '--';
		if(($this->getPercent($hash) != 100) && ($this->torrents[$hash]->get_down_rate() != 0))
			$return = $this->formatETA(ceil((($this->torrents[$hash]->get_size_chunks() - $this->torrents[$hash]->get_completed_chunks()) * $this->torrents[$hash]->get_chunk_size() / 1024) / $this->torrents[$hash]->get_down_rate()));
		
		return $return;
	}
	public function getSize($hash)
	{
		return $this->getCorrectUnits($this->torrents[$hash]->get_size_chunks() * $this->torrents[$hash]->get_chunk_size());
	}
	public function getDone($hash)
	{
		return $this->getCorrectUnits($this->torrents[$hash]->get_completed_chunks() * $this->torrents[$hash]->get_chunk_size());
	}
	public function getTstate($hash)
	{
		if($this->torrents[$hash]->get_state() == 0)
		{
			$return = 'stopped';
		} else {
			if($this->getPercent($hash) != 100){
				$return = 'downloading';
			} else {
				$return = 'seeding';
			}
		}
		if($this->torrents[$hash]->is_open() != 1)
			$return = 'closed';

		if(($this->torrents[$hash]->get_message() != '') && ($this->torrents[$hash]->get_message() != 'Tracker: [Tried all trackers.]'))
			$return = 'message';

		if($this->torrents[$hash]->is_hash_checking() == 1)
			$return = 'chash';
	}
	public function getTstyle($hash)
	{
		switch($this->getTstate($hash))
		{
			case 'downloading':
			$return = 'green';
			break;
			case 'stopped':
			$return = 'black';
			break;
			case 'seeding':
			$return = 'blue';
			break;
			case 'closed':
			$return = 'black';
			break;
			case 'message':
			$return = 'red';
			break;
			case 'chash':
			$return = 'yellow';
			break;
		}
		return $return;
	}
	public function getMessage($hash)
	{
		return $this->torrents[$hash]->get_message();
	}
	public function getTooltipText($hash)
 	{
       if($this->getTstate($hash) == 'message')
           $return = $this->getMessage($hash);
       else
           $return = null;
       return $return;
	}
  public function getCreationDate($hash)
 	{
  	return $this->torrents[$hash]->get_creation_date();
  }
	private function getCorrectUnits($size)
 	{
		$size_units = 'bytes';
		if($size >= 1024)
		{
   		$size /= 1024;
   		$size_units = 'Kb';
		}
		if($size >= 1024)
		{
    	$size /= 1024;
      $size_units = 'Mb';
		}
		if($size >= 1024)
    {
    	$size /= 1024;
      $size_units = 'Gb';
		}
    return round($size, 1) .  $size_units;
  }
	private function formatETA($time)
 	{
 		if (!is_array($periods)) {
       $periods = array (
         'weeks'     => 604800,
         'days'      => 86400,
         'hours'     => 3600,
         'minutes'   => 60,
         );
    }

    $seconds = (float) $time * 1000;
    foreach ($periods as $period => $value) 
    {
      $count = floor($seconds / $value);
      if ($count == 0) 
        continue;

      $values[$period] = $count;
      $seconds = $seconds % $value;
    }

    foreach ($values as $key => $value) 
    {
      $segment_name = substr($key, 0, 1);
      $segment = $value . $segment_name; 
      // If ETA is weeks away, don't display minutes:       
      if ($key == "minutes")
        if ($values["weeks"] >= 1)
          $segment = "";
      
      $array[] = $segment;
    }
     // If ETA is more then 30 weeks away, display "inf" instead of precise ETA: 
    if ($values["weeks"] > 30)
      $str = "inf";
    else
      $str = implode('', $array);
     
    return $str;
  }
}
?>