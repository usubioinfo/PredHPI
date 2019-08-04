#!/usr/bin/env Rscript

rm(list=ls(all=TRUE))


args = commandArgs(trailingOnly=TRUE)
options(stringsAsFactors = FALSE)

if (length(args)<6) {
  stop("At least six arguments must be supplied (input file).n", call.=FALSE)
} else {
  
  #library("GOSim")
  library("GOSemSim")
  library(doParallel)
  library(itertools)  
  
  cores = detectCores()
  if(cores<=1){
   cores = 1
  }

  registerDoParallel(cores)
    
  
  # threshold = 0.2
  # database.specie = 'org.At.tair.db'
  # BP.goDBSem <- godata(database.specie, ont="BP")
  # host.protein.interpro.table = read.delim("/home/cdloaiza/GOtest/hostTest.txt", header = FALSE)
  # pathogen.protein.interpro.table = read.delim("/home/cdloaiza/GOtest/pathogenTest.txt", header = FALSE)
  
  threshold = args[3]
  
  database.specie = args[4]
  
  outfile = args[5]
  
  combineStrategy = args[6]
  
  #MF.goDBSem <- godata(database.specie, ont="MF", computeIC=TRUE)
  #CC.goDBSem <- godata(database.specie, ont="CC", computeIC=TRUE)
  BP.goDBSem <- godata(database.specie, ont="BP")
  
  host.protein.interpro.table = read.table(args[1], header = FALSE, sep="\t", fill = TRUE, col.names= paste("V", seq_len(14), sep="") )
  pathogen.protein.interpro.table = read.table(args[2], header = FALSE, sep="\t", fill = TRUE, col.names=paste("V",seq_len(14), sep="") )


  result = data.frame(0,"Host","Pathogen","Host_GO_Terms","Pathogen_GO_Terms")

  colnames(result) = c("Sim","Host","Pathogen","Host_GO_Terms","Pathogen_GO_Terms")

  
  if(nrow(host.protein.interpro.table) > 0 && nrow(pathogen.protein.interpro.table) > 0){

      host.unique.ids = unique(host.protein.interpro.table[,1])
      pathogen.unique.ids = unique(pathogen.protein.interpro.table[,1])
      
      host.gosets = c()
      pathogen.gosets = c()
      
      for(i in 1:length(host.unique.ids)){
        go.raw.list = host.protein.interpro.table[host.protein.interpro.table$V1==host.unique.ids[i],14]
        go.raw.list = unique(go.raw.list)
        go.raw.list = go.raw.list[go.raw.list!= ""]
        go.raw.list = unlist(strsplit(go.raw.list, "\\|"))
        go.raw.list = unique(go.raw.list)
        host.gosets = c(host.gosets,list(go.raw.list))
      }
      
      for(i in 1:length(pathogen.unique.ids)){
        go.raw.list = pathogen.protein.interpro.table[pathogen.protein.interpro.table$V1==pathogen.unique.ids[i],14]
        go.raw.list = unique(go.raw.list)
        go.raw.list = go.raw.list[go.raw.list!= ""]
        go.raw.list = unlist(strsplit(go.raw.list, "\\|"))
        go.raw.list = unique(go.raw.list)
        pathogen.gosets = c(pathogen.gosets,list(go.raw.list))
      }
      
      ###Calculate sim per each host x pathogen pair
      go.distances = list()
      host.go = c()
      host = c()
      pathogen.go = c()
      pathogen = c()
      ### Select host protein GO set
      allResults = foreach (i=1:length(host.unique.ids),.combine = function(x,y)rbindlist(list(x,y)),.inorder=FALSE,.multicombine=T)%dopar%
      {
	host.go.set=NULL
	gc()        
        host.go.set=host.gosets[[i]]
        ### Select pathogen protein GO set
        
        hostResults = foreach(j=1:length(pathogen.unique.ids),.combine = function(x,y)rbindlist(list(x,y)),.inorder=FALSE,.multicombine= T)%dopar%{
	  pathogen.go.set=NULL
          key=NULL
	  gc()
          pathogen.go.set=pathogen.gosets[[j]]
          key = paste(host.unique.ids[i], pathogen.unique.ids[j], sep ="_")
          #print(key)
          
          value = 0
          
          if(!(is.null(host.go.set) || is.null(pathogen.go.set))){
            value = mgoSim(host.go.set, pathogen.go.set,  semData = BP.goDBSem, measure = "Wang", combine = combineStrategy)
          }

          temp=NULL
	  gc()
          temp= data.table(
	    Sim= value,
	    Host= host.unique.ids[i],
	    Pathogen= pathogen.unique.ids[j],
	    Host_GO_Terms= paste(host.go.set, collapse = ','),
	    Pathogen_GO_Terms= paste(pathogen.go.set, collapse = ',')
	  )
          #as.data.frame(value)
          #colnames(temp) = key
          #temp[2,1]=host.unique.ids[i]
          #temp[3,1]=pathogen.unique.ids[j]
          #temp[4,1]=paste(host.go.set, collapse = ',')
          #temp[5,1]=paste(pathogen.go.set, collapse = ',')          
          temp
          
        }
      }
      
      #result = as.data.frame(t(as.data.frame(allResults)))
      #colnames(result) = c("Sim","Host","Pathogen","Host_GO_Terms","Pathogen_GO_Terms")
      #rownames(result) = NULL
      #allResults = NULL
      gc()

  }


  
  result$Interacting = "NO"
  result[result$Sim>=threshold,6] = "YES"
  
  result =  result[result$Interacting=="YES",]

  result = result[,c(6,2,3,1,4,5)]

  write.table(result, file = outfile, quote=FALSE, sep="\t",  row.names = FALSE )  
}
