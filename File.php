<?php 

class FileTransfer extends UserAction
{
    public static string $collection = 'files';
    protected function action(): Response
    {
        //прислать токен авторизации и файл, обработать на исключения и отправить в папку юзерфайлов с записью в бд служебной информации
        $UploadedFile = $this->request->getUploadedFiles();
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/UserFiles/';
        $dest_path = $uploadFileDir . $newFileName;

        if($fileSize >= 104857600){
            return $this->respondWithError("File Too Big", 400);

        }else {

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                //  ЗДЕСЬ ЗАПИСЬ В БД

                $date = new DateTime();
                $timestamp = $date->getTimestamp();
                $output = [];
                $output['id'] = UUID::v4();
                $output['link'] = $dest_path;
                $output['name'] = $fileName;
                $output['type'] = $fileExtension;
                $output['size'] = $fileSize;
                $output['date'] = $timestamp;
                $output['user'] = $this->user->id;

                $handler = new ArangoDocumentHandler(DB::Get());
                $_id = $handler->insert('files', ArangoDocument::createFromArray($output));

                return $this->respondWithData($output);

            } else {
                return $this->respondWithError("Can't upload file", 500);
            }
        }

    }
}
