<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework
 *
 * $Id: Event.php 10/11/2013 11:35:21 scp@orilla $
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   TeamSpeak3
 * @version   1.1.23
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

namespace TeamSpeak3\Adapter\ServerQuery;

use TeamSpeak3\TeamSpeak3;

/**
 * @class TeamSpeak3_Adapter_ServerQuery_Event
 * @brief Provides methods to analyze and format a ServerQuery event.
 */
class Event implements \ArrayAccess
{

	/**
	 * Stores the event type.
	 *
	 * @var TeamSpeak3_Helper_String
	 */
	protected $type = null;

	/**
	 * Stores the event data.
	 *
	 * @var array
	 */
	protected $data = null;

	/**
	 * Stores the event data as an unparsed string.
	 *
	 * @var TeamSpeak3_Helper_String
	 */
	protected $mesg = null;

	/**
	 * Creates a new TeamSpeak3_Adapter_ServerQuery_Event object.
	 *
	 * @param  TeamSpeak3_Helper_String $evt
	 * @param  TeamSpeak3_Node_Host     $con
	 * @throws TeamSpeak3_Adapter_Exception
	 * @return TeamSpeak3_Adapter_ServerQuery_Event
	 */
	public function __construct(\TeamSpeak3\Helper\String $evt, \TeamSpeak3\Node\Host $con = null)
	{
		if (!$evt->startsWith(TeamSpeak3::EVENT))
		{
			throw new \TeamSpeak3\Adapter\Exception("invalid notification event format");
		}

		list($type, $data) = $evt->split(TeamSpeak3::SEPARATOR_CELL, 2);

		if (empty($data))
		{
			throw new \TeamSpeak3\Adapter\Exception("invalid notification event data");
		}

		$fake	 = new \TeamSpeak3\Helper\String(TeamSpeak3::ERROR . TeamSpeak3::SEPARATOR_CELL . "id" . TeamSpeak3::SEPARATOR_PAIR . 0 . TeamSpeak3::SEPARATOR_CELL . "msg" . TeamSpeak3::SEPARATOR_PAIR . "ok");
		$repl	 = new \TeamSpeak3\Adapter\ServerQuery\Reply(array($data, $fake), $type);

		$this->type	 = $type->substr(strlen(TeamSpeak3::EVENT));
		$this->data	 = $repl->toList();
		$this->mesg	 = $data;

		\TeamSpeak3\Helper\Signal::getInstance()->emit("notifyEvent", $this, $con);
		\TeamSpeak3\Helper\Signal::getInstance()->emit("notify" . ucfirst($this->type), $this, $con);
	}

	/**
	 * Returns the event type string.
	 *
	 * @return TeamSpeak3_Helper_String
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Returns the event data array.
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Returns the event data as an unparsed string.
	 *
	 * @return TeamSpeak3_Helper_String
	 */
	public function getMessage()
	{
		return $this->mesg;
	}

	/**
	 * @ignore
	 */
	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->data) ? TRUE : FALSE;
	}

	/**
	 * @ignore
	 */
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset))
		{
			throw new \TeamSpeak3\Adapter\ServerQuery\Exception("invalid parameter", 0x602);
		}

		return $this->data[$offset];
	}

	/**
	 * @ignore
	 */
	public function offsetSet($offset, $value)
	{
		throw new \TeamSpeak3\Node\Exception("event '" . $this->getType() . "' is read only");
	}

	/**
	 * @ignore
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	/**
	 * @ignore
	 */
	public function __get($offset)
	{
		return $this->offsetGet($offset);
	}

	/**
	 * @ignore
	 */
	public function __set($offset, $value)
	{
		$this->offsetSet($offset, $value);
	}

}