<?php
/**
 * MIT License
 * (c) 2007 - 2017 Heinrich Stamerjohanns
 *
 * A class to handle entries in the errlog_detail database.
 *
 */

namespace Dmake;

use \PDO;

class ErrDetEntry
{
	protected $document_id = 0;
	protected $pos = 0;
	protected $date_created = '';
	protected $errclass = '';
	protected $errtype = '';
	protected $errmsg = '';
	protected $errobject = '';
	protected $md5_errmsg = '';

    /**
     * @return int
     */
    public function getDocumentId()
    {
        return $this->document_id;
    }

    /**
     * @param int $document_id
     */
    public function setDocumentId($document_id)
    {
        $this->document_id = $document_id;
    }

    /**
     * @return int
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @param int $pos
     */
    public function setPos($pos)
    {
        $this->pos = $pos;
    }

    /**
     * @return string
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param string $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * @return string
     */
    public function getErrclass()
    {
        return $this->errclass;
    }

    /**
     * @param string $errclass
     */
    public function setErrclass($errclass)
    {
        $this->errclass = $errclass;
    }

    /**
     * @return string
     */
    public function getErrtype()
    {
        return $this->errtype;
    }

    /**
     * @param string $errtype
     */
    public function setErrtype($errtype)
    {
        $this->errtype = $errtype;
    }

    /**
     * @return string
     */
    public function getErrmsg()
    {
        return $this->errmsg;
    }

    /**
     * @param string $errmsg
     */
    public function setErrmsg($errmsg)
    {
        $this->errmsg = $errmsg;
    }

    /**
     * @return string
     */
    public function getErrObject()
    {
        return $this->errobject;
    }

    /**
     * @param string $errobject
     */
    public function setErrObject($errobject)
    {
        $this->errobject = $errobject;
    }

    /**
     * @return string
     */
    public function getMd5Errmsg()
    {
        return $this->md5_errmsg;
    }

    /**
     * @param string $md5_errmsg
     */
    public function setMd5Errmsg($md5_errmsg)
    {
        $this->md5_errmsg = $md5_errmsg;
    }

    public function __construct($document_id, $target)
    {
        $this->document_id = $document_id;
        $this->target = $target;
    }

    /**
     * saves the current instance to db
     */
    public function save()
	{
		$dao = Dao::getInstance();

		$query = '
			REPLACE	INTO
				errlog_detail
			SET
				document_id	= :document_id,
				pos	= :pos,
				date_created = :date_created,
				target = :target,
				errtype = :errtype,
				errclass = :errclass,
				errmsg = :errmsg,
				errobject = :errobject,
				md5_errmsg = :md5_errmsg';

		$stmt = $dao->prepare($query);
        $stmt->bindValue(':document_id', $this->document_id);
        $stmt->bindValue(':pos', $this->pos);
        $stmt->bindValue(':date_created', $this->date_created);
        $stmt->bindValue(':target', $this->target);
        $stmt->bindValue(':errtype', $this->errtype);
        $stmt->bindValue(':errclass', $this->errclass);
        $stmt->bindValue(':errmsg', $this->errmsg);
        $stmt->bindValue(':errobject', $this->errobject);
        $stmt->bindValue(':md5_errmsg', $this->md5_errmsg);

        $stmt->execute();
	}

    /**
     * creates an ErrDetEntry from $row
     *
     * @param $row
     * @return ErrDetEntry
     */
    public static function fillEntry($row)
    {
        $ede = new self();
        if (isset($row['document_id'])) {
            $ede->document_id = $row['document_id'];
        }
        if (isset($row['date_created'])) {
            $ede->pos = $row['pos'];
        }
        if (isset($row['date_modified'])) {
            $ede->date_created = $row['date_created'];
        }
        if (isset($row['target'])) {
            $ede->target = $row['target'];
        }
        if (isset($row['errtype'])) {
            $ede->errtype = $row['errtype'];
        }
        if (isset($row['errclass'])) {
            $ede->errclass = $row['errclass'];
        }
        if (isset($row['errmsg'])) {
            $ede->errmsg = $row['errmsg'];
        }
        if (isset($row['errobject'])) {
            $ede->errobject = $row['errobject'];
        }
        if (isset($row['md5_errmsg'])) {
            $ede->md5_errmsg = $row['md5_errmsg'];
        }

        return $ede;
    }

    /**
     * checks if entries exist for given document_id
     * @param $document_id
     * @param $target
     * @return bool
     */
	public static function exists($document_id, $target)
	{
		$dao = Dao::getInstance();

		$query = "
			SELECT
				document_id
			FROM
				errlog_detail
			WHERE
				document_id = :document_id
				AND target = :target";

		$stmt = $dao->prepare($query);
        $stmt->bindValue(':document_id', $document_id);
        $stmt->bindValue(':target', $target);
        $stmt->execute();

        // @TODO rowCount() reliable?
		$num = $stmt->rowCount();

		return ($num > 0);
	}

    /**
     * deletes entries by documentId and target
     *
     * @param $document_id
     * @param $target
     */
	public static function deleteByIdAndTarget($document_id, $target)
	{
		$dao = Dao::getInstance();

		$query = "
			DELETE FROM
				errlog_detail
			WHERE
				document_id = :document_id
				AND target = :target";

		$stmt = $dao->prepare($query);
        $stmt->bindValue(':document_id', $document_id);
        $stmt->bindValue(':target', $target);
        $stmt->execute();
	}

    /**
     * Returns the number of entries for given error message.
     *
     * @param $md5ErrMsg
     * @return mixed
     */
    public static function getCountByMd5ErrMsg($md5ErrMsg)
    {
        $dao = Dao::getInstance();

        $query = "
        	SELECT
		        count(*) as numrows
	        FROM
		        errlog_detail
	        WHERE
		        md5_errmsg = :errmsg";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errmsg', $md5ErrMsg);

        $stmt->execute();

        $row = $stmt->fetch();

        $numRows = $row['numrows'];

        return $numRows;
    }

    /**
     * Returns the error messages given by md5ErrMsg.
     * @param $md5ErrMsg
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getByMd5ErrMsg($md5ErrMsg, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                distinct t2.filename,
                t2.date_created,
                t1.errmsg
            FROM
                errlog_detail as t1
            JOIN
                statistic as t2
            ON
                t1.document_id = t2.id
            WHERE
                t1.md5_errmsg = :errmsg
            ORDER BY
                t2.date_created DESC
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errmsg', $md5ErrMsg);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Returns the number of entries for given error class.
     * @param $errClass
     * @return int
     */
    public static function getCountByErrClass($errClass)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct md5_errmsg) as numrows
            FROM
                errlog_detail
            WHERE
                errclass = :errclass";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errclass', $errClass);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the entries by given ErrClass.
     * @param $errClass
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getByErrClass($errClass, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(md5_errmsg) as num,
                errtype,
                md5_errmsg
            FROM
                errlog_detail
            WHERE
                errclass = :errclass
            GROUP BY
                md5_errmsg
            ORDER BY
                num DESC
            LIMIT $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errclass', $errClass);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the number of files by given errClass.
     * @param $errClass
     * @return int
     */
    public static function getFileCountByErrClass($errClass)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct document_id) as numrows
            FROM
                errlog_detail
            WHERE
                errclass = :errclass";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errclass', $errClass);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the file by errClass.
     * @param $errClass
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getFileByErrClass($errClass, $min, $max_pp)
    {
        $dao = Dao::getInstance();
        $query = "
        	SELECT
		        distinct t2.filename,
		        t2.date_created,
		        t1.errmsg
	        FROM
		        errlog_detail as t1
	        JOIN
		        statistic as t2
	        ON
		        t1.document_id = t2.id
	        WHERE
		        t1.errclass = :errclass
	        ORDER BY
		        t2.date_created DESC
	        LIMIT
		        $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errclass', $errClass);

        $stmt->execute();
        return $stmt->fetchAll();

    }

    /**
     * Gets the number of entries by errType.
     * @param $errType
     * @return int
     */
    public static function getCountByErrType($errType)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct md5_errmsg) as numrows
            FROM
                errlog_detail
            WHERE
                errtype = :errtype";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errtype', $errType);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the number of errClass entries by errType.
     * @param $errType
     * @return int
     */
    public static function getCountErrClassByErrType($errType)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct errclass) as numrows
            FROM
                errlog_detail
            WHERE
                errtype = :errtype";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errtype', $errType);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the number of errClass by errType.
     * @param $errType
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getErrClassByErrType($errType, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        $query = "
	        SELECT
		        count(errclass) as num,
		        errclass
	        FROM
		        errlog_detail
	        WHERE
		        errtype = :errtype
	        GROUP BY
		        errclass
	        ORDER BY
		        num DESC
	        LIMIT $min, $max_pp";


        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errtype', $errType);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the number of entries by errType.
     * @param $errType
     * @param $min
     * @param $max_pp
     * @return array
     */
    public static function getByErrType($errType, $min, $max_pp)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(md5_errmsg) as num,
                errtype,
                md5_errmsg
            FROM
                errlog_detail
            WHERE
                errtype = :errtype
            GROUP BY
                md5_errmsg
            ORDER BY
                num DESC
            LIMIT $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errtype', $errType);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the corresponding error message for given md5 string.
     * @param $md5
     * @return array|mixed
     */
    public static function getErrMsgByMd5($md5)
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                errmsg
            FROM
                errlog_detail
            WHERE
                md5_errmsg = :md5
            LIMIT 1";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':md5', $md5, PDO::PARAM_STR);

        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?? [];
    }
}
