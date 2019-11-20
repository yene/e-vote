package main

import (
	"math/rand"
	"os"
	"path/filepath"
	"time"

	"github.com/gocarina/gocsv"
	"github.com/google/uuid"
	"gopkg.in/src-d/go-git.v4"
	. "gopkg.in/src-d/go-git.v4/_examples"
	"gopkg.in/src-d/go-git.v4/plumbing/object"
)

const ff = "abstimmung-11-2019"

// Vote is a line in the CSV
type Vote struct {
	ID       int    `csv:"ID"`
	Desc     string `csv:"Beschreibung"`
	Vote     string `csv:"Stimme"`
	Yes      int    `csv:"Ja"`
	No       int    `csv:"Nein"`
	Withhold int    `csv:"Vorenthalten"`
}

// Basic example of how to commit changes to the current branch to an existing
// repository.
func main() {
	rand.Seed(time.Now().UTC().UnixNano())

	CheckArgs("<directory>")
	directory := os.Args[1]
	r := createGit(directory)
	path := filepath.Join(directory, ff+".csv")
	setupCSV(path)

	for i := 0; i < 1000; i++ {
		vote(path, r)
	}

}

func setupCSV(path string) {
	template := []Vote{
		Vote{ID: 1, Desc: "Abschaffung Militär"},
		Vote{ID: 2, Desc: "Revision IV        "},
		Vote{ID: 3, Desc: "Steuern Anpassung  "},
		Vote{ID: 4, Desc: "Ausländer Raus     "},
		Vote{ID: 5, Desc: "Kirchglocken Weg   "},
	}

	if !fileExists(path) {
		csvOut, err := os.Create(path)
		CheckIfError(err)
		err = gocsv.MarshalFile(&template, csvOut)
		CheckIfError(err)
		csvOut.Close()
	}
}

func createGit(directory string) *git.Repository {
	// Opens an already existing repository.
	r, err := git.PlainOpen(directory)
	if err != nil {
		r, err = git.PlainInit(directory, false)
		CheckIfError(err)
	}
	return r
}

func vote(path string, r *git.Repository) {
	csvFile, err := os.OpenFile(path, os.O_RDWR, os.ModePerm)
	CheckIfError(err)

	votes := []*Vote{}
	if err := gocsv.UnmarshalFile(csvFile, &votes); err != nil {
		panic(err)
	}

	for _, v := range votes {
		// TODO: validate total of each row
		//v := votes[i]
		which := rand.Intn(3)
		if which == 0 {
			v.Yes++
			v.Vote = "JA"
		} else if which == 1 {
			v.No++
			v.Vote = "NEIN"
		} else {
			v.Withhold++
			v.Vote = "LEER"
		}
	}

	if err := csvFile.Truncate(0); err != nil { // clear the file
		panic(err)
	}
	if _, err := csvFile.Seek(0, 0); err != nil { // Go to the start of the file
		panic(err)
	}
	err = gocsv.MarshalFile(&votes, csvFile)
	CheckIfError(err)
	csvFile.Close()

	w, err := r.Worktree()
	CheckIfError(err)

	_, err = w.Add(filepath.Base(path))
	CheckIfError(err)

	uuid := uuid.New().String()

	// randomize commit time 0-5 minutes, it looks confusing in the output..
	delay := rand.Intn(5 * 60)

	// it looks cooler with a uuid name
	_, err = w.Commit(uuid, &git.CommitOptions{
		Author: &object.Signature{
			Name:  uuid,
			Email: "",
			When:  time.Now().Add(time.Second * time.Duration(delay)),
		},
	})

	CheckIfError(err)
	/*
		// Prints the current HEAD to verify that all worked well.
		//Info("git show -s")
		obj, err := r.CommitObject(commit)
		CheckIfError(err)
		fmt.Println(obj)*/
}

func fileExists(filename string) bool {
	info, err := os.Stat(filename)
	if os.IsNotExist(err) {
		return false
	}
	return !info.IsDir()
}
