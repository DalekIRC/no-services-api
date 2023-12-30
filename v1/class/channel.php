<?php

class Channel {

	public static function Register($channel, &$error, &$errorcode)
	{
		$conn = sqlnew();
		$stmt = $conn->prepare("INSERT INTO userv_channel (channel_name) VALUES (:name)");
		$stmt->execute(["name" => $channel]);
		return $stmt->rowCount();
	}
	
	public static function find($name)
	{
		$chan =  self::get($name);
		if (!$chan)
			return null;
		else
			$chan->meta = self::get_meta($chan->id);
		return $chan;
	}

	/** Helper function for "find" */
	public static function get($lookup)
	{
		$name = strtolower($lookup);
		$conn = sqlnew();
		$stmt = $conn->prepare("SELECT * FROM userv_channel WHERE LOWER(channel_name) = :name LIMIT 1");
		$stmt->execute(["name" => $name]);
		if ($stmt->rowCount())
		{
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return (object)$results;
		}
		return null;
	}

	public static function get_meta($id)
	{
		$conn = sqlnew();
		$stmt = $conn->prepare("SELECT * FROM userv_channel_meta WHERE channel_id = :id LIMIT 1");
		$stmt->execute(["id" => $id]);
		if ($stmt->rowCount())
		{
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $results;
		}
		return null;
	}
	public static function list()
	{
		$conn = sqlnew();
		$sql = "SELECT * FROM userv_channel";
		$ret = $conn->query($sql);
		return $ret->fetchAll(PDO::FETCH_ASSOC) ?? null;
	}
}
