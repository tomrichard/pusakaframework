package main

import (
    "archive/zip"
    "fmt"
    "io"
    "log"
    "os"
    "os/exec"
    "os/user"
    "path/filepath"
    "strings"
    "runtime"
)

func main() {

	usr, err := user.Current()
	if err != nil {
		log.Fatal( err )
	}
	
	home := usr.HomeDir + string(os.PathSeparator) + ".pusaka";

	if _, err := os.Stat( home ); os.IsNotExist(err) {
		os.MkdirAll(home, os.ModePerm)
	}else {		
	}

	fmt.Println("Check - PHP CLI 7.2");

	cmd 		:= exec.Command("php", "-v")
	_, 		err  = cmd.StdoutPipe()
	
	if err != nil {
		install_cli(home)
		return
	}else if err := cmd.Start(); err != nil {
		install_cli(home)
		return
	}else if err := cmd.Wait(); err != nil {
		install_cli(home)
		return
	}

	args 	  := os.Args

	cmd  	   = exec.Command("php", args...)
    
    cmd.Stdout = os.Stdout
	cmd.Stderr = os.Stderr
	err 	   = cmd.Run()
	if err != nil {
		log.Fatalf("cli failed with %s\n", err)
	}

}

func install_cli( home string ) {

	if runtime.GOOS == "windows" {
	
		var from string
		var dest string
		var path string

		fmt.Println("Windows detected !")

		from = home + "\\windows-php7.2.zip"
		dest = "c:/php"

		// check file are 100% downloaded
		//------------------------------------------------------
		if _, err := os.Stat( from ); os.IsNotExist(err) {
			
			// download
			url 	:= "https://repos.xdevpusaka.com/windows-php7.2.zip"
			saveto 	:= from

			DownloadFile(url, saveto)

		}

		_, err := Unzip(from, dest, func(file string ) {
	    	fmt.Println("Unzipped: " + file)
	    })

	    if err != nil {
	        log.Fatal(err)
	    }

	    path 	= os.Getenv("PATH")

	    path 	= "C:\\php;" + path;

	    //fmt.Println(path);

		cmd 	:= exec.Command("setx", "PATH", path)
		_, 	err = cmd.StdoutPipe()
		
		if err != nil {
			log.Fatal(err)
		}else if err := cmd.Start(); err != nil {
			log.Fatal(err)
		}else if err := cmd.Wait(); err != nil {
			log.Fatal(err)
		}

		fmt.Println("Ok!")
		fmt.Println("Please close and reopen Terminal !")
		fmt.Println("===================================")

	}else if runtime.GOOS == "linux" {
		
		fmt.Println("Linux detected !")

		// from = "zipfile/linux-php7.2.zip"
		// dest = "/var/"

	}

}

func DownloadFile( url string, dest string ) {

	cmd 		:= exec.Command("system\\bin\\curl", "-o", dest, url)

    fmt.Println("Download PHP CLI 7.2 := ")

    cmd.Stdout  = os.Stdout
	cmd.Stderr  = os.Stderr
	err 	   := cmd.Run()
	if err != nil {
		log.Fatalf("cli failed with %s\n", err)
	}

}

// Unzip will decompress a zip archive, moving all files and folders
// within the zip file (parameter 1) to an output directory (parameter 2).
func Unzip(src string, dest string, progress func(file string) ) ([]string, error) {

    var filenames []string

    r, err := zip.OpenReader(src)
    if err != nil {
        return filenames, err
    }
    defer r.Close()

    for _, f := range r.File {

        // Store filename/path for returning and using later on
        fpath := filepath.Join(dest, f.Name)

        // Check for ZipSlip. More Info: http://bit.ly/2MsjAWE
        if !strings.HasPrefix(fpath, filepath.Clean(dest)+string(os.PathSeparator)) {
            return filenames, fmt.Errorf("%s: illegal file path", fpath)
        }

        filenames = append(filenames, fpath)

        if f.FileInfo().IsDir() {
            // Make Folder
            os.MkdirAll(fpath, os.ModePerm)
            continue
        }

        // Make File
        if err = os.MkdirAll(filepath.Dir(fpath), os.ModePerm); err != nil {
        	return filenames, err
        }

        outFile, err := os.OpenFile(fpath, os.O_WRONLY|os.O_CREATE|os.O_TRUNC, f.Mode())
        if err != nil {
            return filenames, err
        }

        rc, err := f.Open()
        if err != nil {
            return filenames, err
        }

        _, err = io.Copy(outFile, rc)

        // Close the file without defer to close before next iteration of loop
        outFile.Close()
        rc.Close()

        progress(fpath);

        if err != nil {
            return filenames, err
        }
    }
    return filenames, nil
}