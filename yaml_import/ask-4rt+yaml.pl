#!/usr/bin/perl
#
# Copyright (C) 2011 PSMN / ENS de Lyon - LT
# This script is licenced under CeCILL, see COPYING 
# or http://www.cecill.info/ (french GPL v2 equivalent)
#
# $Id: ask-4rt+yaml.pl 112 2011-06-14 13:50:09Z gruiick $
#
# Authors
#  . Loïs Taulelle <lois dot taulelle at ens-lyon dot fr>
#
# Parameters understood:
#  [hostbase] : the base of hostname (ex: dl165lin)
#  [start] : numerotation start (ex: 10)
#  [end] : the end... this is the end (ex: 82)
# => process a list from dl165lin10 to dl165lin82
# or
#  [hostname] & --uniq : when there's only one host to deal with
#
# Environment:
#  Unix only, need following CPAN modules: 
#  YAML::Tiny Log::Logger File::Tools (for File::Basename File::Copy File::Path) 
#  File::List Parse::DMIDecode
#
# Configuration:
#  yes, see $debug and $log variables
#
use strict;
use warnings;
use YAML::Tiny;
use Log::Logger;
#use File::Tools;
use File::Basename;
use File::Copy;
use File::Path;
use File::List;
use Parse::DMIDecode ();

## Create objects and set environment
# Configuration
my $log = "no"; # yes|no
my $debug = "yes"; # yes|no
my $fqdn = "ens-lyon.fr";

# global scalars & objects
my $scriptname = basename($0, ".pl");
my $hostbase = "";
my $start = "";
my $end = "";
my $uniqueness = "";
my @hostArray =();
my $dmi_decoder = Parse::DMIDecode->new();
my $yaml = YAML::Tiny->new;

# init logs
my $logline = new Log::Logger;
my $logfile = "";
if ( $log eq "yes" ) 
{
  $logfile = "$scriptname.log";
  print ("There will be logs: $logfile \n\n");
}
else 
{
# TODO verbose and silent mode ?
  $logfile = "/dev/null";
  print ("no logs! \n");
}
$logline->open_append("$logfile");
my $date = &getDate();
$logline->log_print("\t Job start at $date \n");

# init debug
if ( $debug eq "yes" )
{
  use Data::Dumper;
  $logline->log_print("\t DEBUG MODE ON \n");
}


## main program
# proceeding arguments
if ( $#ARGV == -1 or $#ARGV != 2 )
{
  if ( $#ARGV == 1 )
  {
    if ( $ARGV[1] eq "--uniq" )
    {

      if ( $debug eq "yes" )
      {
        print ("Arguments: \n");
        print Dumper(@ARGV);
      }
      if ( $log eq "yes" )
      {
        $logline->log_print("$scriptname called with: @ARGV \n");
      }

      $uniqueness = "1";
      $hostArray[0] = $ARGV[0];
      &main();
    }
    else
    {
      &printUsage();
    }
  }
  else
  {
    &printUsage();
  }
}
else # assuming $#ARGV == 2
{

  if ( $debug eq "yes" )
  {
    print ("Arguments: \n");
    print Dumper(@ARGV);
  }
  if ( $log eq "yes" )
  {
    $logline->log_print("$scriptname called with: @ARGV \n");
  }

  $hostbase = $ARGV[0];
  $start = $ARGV[1];
  $end = $ARGV[2];
  $uniqueness = "0";
  &setHostList();
  &main();
}

## subs

sub main
{
  if ( $uniqueness eq "0" )
  {
    foreach my $i ($start .. $end)
    {
      my $host_os = &getOS($hostArray[$i]);
      &getSMBios($hostArray[$i],$host_os);
      &getIfConfig($hostArray[$i],$host_os);
      &writeYaml($hostArray[$i]);
    }
  }
  elsif ( $uniqueness eq "1" )
  {
    my $host_os = &getOS($hostArray[0]);
    &getSMBios($hostArray[0],$host_os);
    &getIfConfig($hostArray[0],$host_os);
    &writeYaml($hostArray[0]);
  }
}

sub printUsage
{
  $logline->log_print("\n\t TOO FEW OR MANY ARGUMENTS! \n");
  $logline->log_print("Usage: $scriptname.pl [hostbase] [start] [end]");
  $logline->log_print("or");
  $logline->fail("Usage: $scriptname.pl [hostname] --uniq");
}


sub writeYaml
# generate the yaml structures, and write it to yaml file
# take $hostArray[$i] as filename
# see skel.yaml
{
  my $filename = (my $host = $_[0]);
  $yaml->[0]->{name} = $_[0];
  $yaml->[0]->{parameters}->{fqdn} = "$host.$fqdn";
  $yaml->write( "$filename.yaml" ) || $logline->fail("\t cannot write $filename!");

  if ( $debug eq "yes" )
  {
    print ("$filename.yaml writed successfully \n");
  }
  if ( $log eq "yes" )
  {
    $logline->log_print("$filename.yaml writed successfully \n");
  }
}

sub setHostList
# generate the hostnames list from call arguments
# sub take no argument
{
  for my $i ($start .. $end)
  {
    $hostArray[$i] = "$hostbase$i";

    if ( $debug eq "yes" )
    {
      print ("\t $hostArray[$i] \n");
    }
  }
}

sub getOS
# ask host for OS release, return OS type
# my $os = &getOS($host);
{
  my $host = $_[0];
  my $os_internal = "sun"; # pick one, cannot be NULL.
  my $release = "";
  my @release_internal = qx(ssh $host cat /etc/release); # have to start with something

  if ( $release_internal[0] eq "" )
  {
    @release_internal = qx(ssh $host cat /etc/redhat-release);
# TODO debian ? others ? BSD's ? LSB ?
# Xen/kvm/vmware/vbox has something to deal with:
# $yaml->[0]->{parameters}->{hypervisor} = "No|Yes";

    if ( $release_internal[0] =~ /CentOS/i )
    {
      $os_internal = "centos";
      $release = $release_internal[0];
    }
    elsif ( $release_internal[0] =~ /Red Hat/i )
    {
      $os_internal = "redhat";
      $release = $release_internal[0];
    }
  }
  elsif ( $release_internal[0] =~ /Solaris/i )
  {
    $os_internal = "sun";
    $release = $release_internal[0];
  }
  $release =~ s/^\s+//; $release =~ s/\s+$//; # cleanup result

  # save this to yaml
  $yaml->[0]->{parameters}->{operatingsystem} = $release;
  my ($digits) = $release =~ /(\d+(?:\.)?\d+)/;
  $yaml->[0]->{parameters}->{operatingsystemrelease} = $digits;
  $yaml->[0]->{parameters}->{hypervisor} = "No"; # fixed, for now
  
  if ( $debug eq "yes" )
  {
    print ("$release, $digits\n");
  }
  if ( $log eq "yes" )
  {
    $logline->log_print("$host.$fqdn: $release, $digits\n");
  }

  return $os_internal;
}

sub getSMBios
# ask host for dmidecode information
# &getSMBios($host,$os);
{
  my $host = $_[0];
  my $os = $_[1];
  my $product_name = "";
  my $serial_number = "";
  my $uuid = "";

  if ( $os eq "sun" )
# Parse::dmidecode doesn't parse Oracle/Sun smbios output, so back to basics.
  {
    my @bios_raw = qx(ssh uroot\@$host smbios);
    my $biosLine = "";
    my $pn_state = (my $sn_state = (my $uu_state = "0"));

    foreach $biosLine (@bios_raw)
    {
      if ( $biosLine =~ /Product:/ )
      {
        if ( $pn_state == "0" )
        {
          $product_name = $biosLine;
          $product_name =~ s/\s+Product:\s+//; chomp $product_name; # cleanup
          
          if ( $debug eq "yes" )
          {
            print ("$product_name \n");
          }
          $pn_state = "1";
        }
        else {next;} # don't waste time with other lines
      }
      elsif ( $biosLine =~ /Serial Number:/ )
      {
        if ( $sn_state == "0" )
        {
          $serial_number = $biosLine;
          $serial_number =~ s/\s+Serial Number:\s+//; chomp $serial_number;
          
          if ( $debug eq "yes" )
          {
            print ("$serial_number \n");
          }
          $sn_state = "1";
        }
        else {next;} # don't waste time with other lines
      }
      elsif ( $biosLine =~ /UUID:/ )
      {
        if ( $uu_state == "0" )
        {
          $uuid = $biosLine;
          $uuid =~ s/\s+UUID:\s+//; chomp $uuid;

          if ( $debug eq "yes" )
          {
            print ("$uuid \n");
          }
          $uu_state = "1";
        }
        else {next;} # don't waste time with other lines
      }
    }
  }
  else
  # Welcome to Parse::dmidecode world
  {
    $dmi_decoder->parse( qx(ssh $host dmidecode) );
    $product_name = $dmi_decoder->keyword("system-product-name");
    $serial_number = $dmi_decoder->keyword("system-serial-number");
    $uuid = $dmi_decoder->keyword("system-uuid");
  }
  # save this to yaml
  $yaml->[0]->{parameters}->{productname} = $product_name;
  $yaml->[0]->{parameters}->{serialnumber} = $serial_number;
  $yaml->[0]->{parameters}->{uuid} = $uuid;
  
  if ( $debug eq "yes" )
  {
    print ("$host: $product_name ; $serial_number ; $uuid ; $os \n");
  }
  if ( $log eq "yes" )
  {
    $logline->log_print("$host: $product_name ; $serial_number ; $uuid ; $os \n");
  }
}

sub getIfConfig
# ask host for IPv4 IF informations
# &getIfConfig($host,$os);
{
  my $host = $_[0];
  my $os = $_[1];
  my @ifname = ();
  my @ifip = ();
  my @iftype = (); # for future uses (maybe...)
  my @ifmac = ();
  my $ifNstate = (my $ifIstate = (my $ifTstate = (my $ifMstate = "0")));
  my @ifconfigRaw = ();
  my $ifconfigLine = "";
  my $ifTlist = "";

  if ( $os eq "sun" )
# specific sort/regexp for SunOS, ifconfig output's not "standard"
  {
    @ifconfigRaw = qx(ssh uroot\@$host ifconfig -a);

    foreach $ifconfigLine (@ifconfigRaw)
    {
      if ( $ifconfigLine =~ /^(\S+)\:/ )
      { 
        if ( $ifconfigLine =~ /^lo/ ) 
        # interface is local, don't keep
        {
          next;
        }
        elsif ( $ifconfigLine =~ /^(\S+\:[0-9]{1})\:(\s+)/ )
        # interface is an alias, doesn't have MAC
        {
          $ifname[$ifNstate] = $1;
          $ifname[$ifNstate] =~ s/\:/\./; # ":" is yaml reserved, so we use "."
          $iftype[$ifTstate] = "Ethernet";
          $ifmac[$ifMstate] = $ifmac[$ifMstate-1];

          if ( $debug eq "yes" )
          {
            print ("$host: $ifname[$ifNstate] \n");
            print ("$host: $iftype[$ifTstate] = $ifmac[$ifMstate] \n");
          }
          if ( $log eq "yes" )
          {
            $logline->log_print("$host: $ifname[$ifNstate] ; $iftype[$ifTstate] ; $ifmac[$ifMstate] \n");
          }

          $ifNstate++;
          $ifTstate++;
          $ifMstate++;
        }
        else
        # assume real interface
        {
          $ifname[$ifNstate] = $1;

          if ( $debug eq "yes" )
          {
            print ("$host: $ifname[$ifNstate] \n");
          }
          if ( $log eq "yes" )
          {
            $logline->log_print("$host: $ifname[$ifNstate] \n");
          }
          $ifNstate++;
        }
      }
      elsif ( $ifconfigLine =~ /inet (?:addr\:)?(\d+(?:\.\d+){3})/ )
      {
        if ( $1 =~ /127\.0\.0\.1/ )
        # IP is local, don't keep
        {
          next;
        }
        else
        {
          $ifip[$ifIstate] = $1;

          if ( $debug eq "yes" )
          {
            print ("$host: $ifip[$ifIstate] \n");
          }
          if ( $log eq "yes" )
          {
            $logline->log_print("$host: $ifip[$ifIstate] \n");
          }
          $ifIstate++;
        }
      }
      elsif ( $ifconfigLine =~ /(\s+)ether(\s+)/ )
      # real interface have MAC
      {
        $iftype[$ifTstate] = "Ethernet";
        $ifconfigLine =~ s/\s+ether\s+//; chomp $ifconfigLine;
        $ifmac[$ifMstate] = $ifconfigLine;
        $ifmac[$ifMstate] =~ s/\s+$//; # remove trailing spaces, chomp does not make it.

        if ( $debug eq "yes" )
        {
          print ("$host: $iftype[$ifTstate] = $ifmac[$ifMstate] \n");
        }
        if ( $log eq "yes" )
        {
          $logline->log_print("$host: $iftype[$ifTstate] ; $ifmac[$ifMstate] \n");
        }
        $ifTstate++;
        $ifMstate++;
      }
      else {next;} # don't waste time with other lines
    }
  }
  else
  # welcome to "linux compliant" ifconfig output
  # cpan really lacks a Parse::ifconfig :o)
  {
    @ifconfigRaw = qx(ssh $host ifconfig);

    foreach $ifconfigLine (@ifconfigRaw)
    {
      if ( $ifconfigLine =~ /^(\S+)/ )
      {
        if ( $ifconfigLine =~ /^lo/ )
        # interface is local, don't keep
        {
          next;
        }
        elsif ( $ifconfigLine =~ /^(\S+(?:\:)?[0-9]{1}) (\s+)/ )
        # interface is real or alias
        {
          $ifname[$ifNstate] = $1;
          $ifname[$ifNstate] =~ s/\:/\./; # ":" is yaml reserved, so we use "."

          if ( $ifconfigLine =~ /Link encap:(.+)\s+HWaddr\s+(\S+)/ )
          # hardware address
          {
            $iftype[$ifTstate] = $1; chomp $iftype[$ifTstate];
# TODO filter infiniband... if infiniband: next; else: keep;
            $ifmac[$ifMstate] = $2;
          }

          if ( $debug eq "yes" )
          {
            print ("$host: $ifname[$ifNstate] \n");
            print ("$host: $iftype[$ifTstate] = $ifmac[$ifMstate] \n");
          }
          if ( $log eq "yes" )
          {
            $logline->log_print("$host: $ifname[$ifNstate] ; $iftype[$ifTstate] ; $ifmac[$ifMstate] \n");
          }

          $ifNstate++;
          $ifTstate++;
          $ifMstate++;
        }
      }
      elsif ( $ifconfigLine =~ /inet (?:addr\:)?(\d+(?:\.\d+){3})/ )
      {
        if ( $1 =~ /127\.0\.0\.1/ )
        # do not want local IP
        {
          next;
        }
        else
        {
          $ifip[$ifIstate] = $1;
          
          if ( $debug eq "yes" )
          {
            print ("$host: $ifip[$ifIstate] \n");
          }
          if ( $log eq "yes" )
          {
            $logline->log_print(" ");
          }

          $ifIstate++;
        }
      }
      else {next;} # don't waste time with other lines
    }
  }

  if ( $debug eq "yes" )
  {
    print Dumper(@ifname);
    print Dumper(@ifip);
    print Dumper(@iftype);
    print Dumper(@ifmac);
  }

# save this to yaml
# $yaml->[0]->{parameters}->{interfaces} is a list
# other network parameters are {interfaces} dependant
  foreach my $i (0 .. $#ifname)
  {
    $ifTlist = "$ifTlist"."${ifname[$i]},";
    print ("$ifTlist \n");
    my $foo = "ipaddress_"."${ifname[$i]}";
    $yaml->[0]->{parameters}->{$foo} = $ifip[$i];
    my $bar = "macaddress_"."${ifname[$i]}";
    $yaml->[0]->{parameters}->{$bar} = $ifmac[$i];
  }
  $ifTlist =~ s/,$//; # remove trailing comma
  $yaml->[0]->{parameters}->{interfaces} = $ifTlist ;

  if ( $debug eq "yes" )
  {
    print Dumper($yaml);
  }
}

sub getDate
# return date in human-readable format for logs
{
  my $result = `date +%Y%m%d-%H:%M`;
  chomp($result);
  return $result;
}

