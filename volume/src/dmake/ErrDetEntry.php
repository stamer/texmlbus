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
	protected $documentId = 0;
	protected $pos = 0;
	protected $dateCreated = '';
	protected $target;
	protected $errClass = '';
	protected $errType = '';
	protected $errMsg = '';
	protected $errObject = '';
	protected $md5ErrMsg = '';

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    public function setDocumentId(int $documentId): void
    {
        $this->documentId = $documentId;
    }

    public function getPos(): ?int
    {
        return $this->pos;
    }

    public function setPos(?int $pos): void
    {
        $this->pos = $pos;
    }

    public function getDateCreated(): ?string
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?string $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getErrClass(): ?string
    {
        return $this->errClass;
    }

    public function setErrClass(?string $errClass): void
    {
        $this->errClass = $errClass;
    }

    public function getErrType(): ?string
    {
        return $this->errType;
    }

    public function setErrType(?string $errType): void
    {
        $this->errType = $errType;
    }

    public function getErrMsg(): ?string
    {
        return $this->errMsg;
    }

    public function setErrMsg(?string $errMsg): void
    {
        $this->errMsg = $errMsg;
    }

    /**
     * @return string
     */
    public function getErrObject(): ?string
    {
        return $this->errObject;
    }

    public function setErrObject(string $errObject): void
    {
        $this->errObject = $errObject;
    }

    public function getMd5ErrMsg(): ?string
    {
        return $this->md5ErrMsg;
    }

    public function setMd5ErrMsg(?string $md5ErrMsg): void
    {
        $this->md5ErrMsg = $md5ErrMsg;
    }

    public function __construct(int $documentId, string $target)
    {
        $this->documentId = $documentId;
        $this->target = $target;
    }

    /**
     * saves the current instance to db
     */
    public function save(): bool
	{
		$dao = Dao::getInstance();

		$query = '
			REPLACE	INTO
				errlog_detail
			SET
				document_id	= :documentId,
				pos	= :pos,
				date_created = :dateCreated,
				target = :target,
				errtype = :errType,
				errclass = :errClass,
				errmsg = :errMsg,
				errobject = :errObject,
				md5_errmsg = :md5ErrMsg';

		$stmt = $dao->prepare($query);
        $stmt->bindValue(':documentId', $this->documentId);
        $stmt->bindValue(':pos', $this->pos);
        $stmt->bindValue(':dateCreated', $this->dateCreated);
        $stmt->bindValue(':target', $this->target);
        $stmt->bindValue(':errtype', $this->errType);
        $stmt->bindValue(':errclass', $this->errClass);
        $stmt->bindValue(':errmsg', $this->errMsg);
        $stmt->bindValue(':errobject', $this->errObject);
        $stmt->bindValue(':md5_errmsg', $this->md5ErrMsg);

        return $stmt->execute();
	}

    /**
     * creates an ErrDetEntry from $row
     */
    public static function fillEntry(array $row): ErrDetEntry
    {
        $ede = new self($row['document_id'], $row['target']);
        if (isset($row['date_created'])) {
            $ede->pos = $row['pos'];
        }
        if (isset($row['date_modified'])) {
            $ede->dateCreated = $row['date_created'];
        }
        if (isset($row['errtype'])) {
            $ede->errType = $row['errtype'];
        }
        if (isset($row['errclass'])) {
            $ede->errClass = $row['errclass'];
        }
        if (isset($row['errmsg'])) {
            $ede->errMsg = $row['errmsg'];
        }
        if (isset($row['errobject'])) {
            $ede->errObject = $row['errobject'];
        }
        if (isset($row['md5_errmsg'])) {
            $ede->md5ErrMsg = $row['md5_errmsg'];
        }

        return $ede;
    }

    /**
     * checks if entries exist for given document_id
     */
	public static function exists(int $documentId, string $target): bool
	{
		$dao = Dao::getInstance();

		$query = "
			SELECT
				document_id
			FROM
				errlog_detail
			WHERE
				document_id = :documentId
				AND target = :target";

		$stmt = $dao->prepare($query);
        $stmt->bindValue(':documentId', $documentId);
        $stmt->bindValue(':target', $target);
        $stmt->execute();

        // @TODO rowCount() reliable?
		$num = $stmt->rowCount();

		return ($num > 0);
	}

    /**
     * deletes entries by documentId and target
     */
	public static function deleteByIdAndTarget(int $documentId, string $target): bool
	{
		$dao = Dao::getInstance();

		$query = "
			DELETE FROM
				errlog_detail
			WHERE
				document_id = :documentId
				AND target = :target";

		$stmt = $dao->prepare($query);
        $stmt->bindValue(':documentId', $documentId);
        $stmt->bindValue(':target', $target);
        return $stmt->execute();
	}

    /**
     * Returns the number of entries for given error message.
     */
    public static function getCountByMd5ErrMsg(int $md5ErrMsg): int
    {
        $dao = Dao::getInstance();

        $query = "
        	SELECT
		        count(*) as numrows
	        FROM
		        errlog_detail
	        WHERE
		        md5_errmsg = :md5ErrMsg";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':md5ErrMsg', $md5ErrMsg);

        $stmt->execute();

        $row = $stmt->fetch();

        return $row['numrows'];
    }

    /**
     * Returns the error messages given by md5ErrMsg.
     */
    public static function getByMd5ErrMsg(string $md5ErrMsg, int $min, int $max_pp): array
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
                t1.md5_errmsg = :md5EerrMsg
            ORDER BY
                t2.date_created DESC
            LIMIT
                $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':md5ErrMsg', $md5ErrMsg);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Returns the number of entries for given error class.
     */
    public static function getCountByErrClass(string $errClass): int
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct md5_errmsg) as numrows
            FROM
                errlog_detail
            WHERE
                errclass = :errClass";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errClass', $errClass);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the entries by given ErrClass.
     */
    public static function getByErrClass(string $errClass, int $min, int $max_pp): array
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
                errclass = :errClass
            GROUP BY
                md5_errmsg
            ORDER BY
                num DESC
            LIMIT $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errClass', $errClass);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the number of files by given errClass.
     */
    public static function getFileCountByErrClass(string $errClass): int
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct document_id) as numrows
            FROM
                errlog_detail
            WHERE
                errclass = :errClass";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errClass', $errClass);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the files by errClass.
     */
    public static function getFileByErrClass(string $errClass, int $min, int $max_pp): array
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
		        t1.errclass = :errClass
	        ORDER BY
		        t2.date_created DESC
	        LIMIT
		        $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errClass', $errClass);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the number of entries by errType.
     */
    public static function getCountByErrType(string $errType): int
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct md5_errmsg) as numrows
            FROM
                errlog_detail
            WHERE
                errtype = :errType";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errType', $errType);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the number of errClass entries by errType.
     */
    public static function getCountErrClassByErrType(string $errType): int
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                count(distinct errclass) as numrows
            FROM
                errlog_detail
            WHERE
                errtype = :errType";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errType', $errType);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['numrows'] ?? 0;
    }

    /**
     * Gets the number of errClass entries by errType.
     */
    public static function getErrClassByErrType(
        string $errType,
        int $min,
        int $max_pp): array
    {
        $dao = Dao::getInstance();

        $query = "
	        SELECT
		        count(errclass) as num,
		        errclass
	        FROM
		        errlog_detail
	        WHERE
		        errtype = :errType
	        GROUP BY
		        errclass
	        ORDER BY
		        num DESC
	        LIMIT $min, $max_pp";


        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errType', $errType);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the number of entries by errType.
     */
    public static function getByErrType(
        string $errType,
        int $min,
        int $max_pp): array
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
                errtype = :errType
            GROUP BY
                md5_errmsg
            ORDER BY
                num DESC
            LIMIT $min, $max_pp";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':errType', $errType);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gets the corresponding error message for given md5 string.
     */
    public static function getErrMsgByMd5(string $md5ErrMsg): array
    {
        $dao = Dao::getInstance();

        $query = "
            SELECT
                errmsg
            FROM
                errlog_detail
            WHERE
                md5_errmsg = :md5ErrMsg
            LIMIT 1";

        $stmt = $dao->prepare($query);
        $stmt->bindValue(':md5ErrMsg', $md5ErrMsg, PDO::PARAM_STR);

        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?? [];
    }
}
